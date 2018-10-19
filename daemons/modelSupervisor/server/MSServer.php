<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/01/18
 * Time: 04:51
 */

namespace wfw\daemons\modelSupervisor\server;

use wfw\daemons\modelSupervisor\server\components\requests\IClientDeniedRequest;
use wfw\daemons\modelSupervisor\server\components\requests\IMSServerComponentRequest;
use wfw\daemons\modelSupervisor\server\components\responses\IMSServerComponentResponse;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponent;
use wfw\daemons\modelSupervisor\server\environment\IMSServerEnvironment;
use wfw\daemons\modelSupervisor\server\errors\AccessDenied;
use wfw\daemons\modelSupervisor\server\errors\ExternalShutdown;
use wfw\daemons\modelSupervisor\server\errors\MSServerFailure;
use wfw\daemons\modelSupervisor\server\errors\MustBeLogged;
use wfw\daemons\modelSupervisor\server\errors\NoHandlerForRequest;
use wfw\daemons\modelSupervisor\server\errors\UnknownRequest;
use wfw\daemons\modelSupervisor\server\errors\UserNotFound;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;
use wfw\daemons\modelSupervisor\server\requests\admin\IMSServerAdminRequest;
use wfw\daemons\modelSupervisor\server\requests\admin\ShutdownMSServerRequest;
use wfw\daemons\modelSupervisor\server\requests\LoginRequest;
use wfw\daemons\modelSupervisor\server\requests\LogoutRequest;
use wfw\daemons\modelSupervisor\server\responses\AccessGranted;
use wfw\daemons\modelSupervisor\server\responses\InvalidRequestError;
use wfw\daemons\modelSupervisor\server\responses\RequestError;
use wfw\daemons\modelSupervisor\server\responses\RequestTimeout;
use wfw\daemons\modelSupervisor\socket\data\errors\MSServerDataParsingFailure;
use wfw\daemons\modelSupervisor\socket\data\MSServerDataParser;
use wfw\daemons\modelSupervisor\socket\data\MSServerDataParserResult;
use wfw\daemons\modelSupervisor\socket\io\MSServerSocketIO;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;

use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  MSServer version 2.0
 */
final class MSServer {
	/** @var IMSServerEnvironment $_environment */
	private $_environment;
	/** @var UUID $_serverKey */
	private $_serverKey;
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var string $_errorLogsFile */
	private $_errorLogsFile;
	/** @var resource $_acquiredLockFile */
	private $_acquiredLockFile;
	/** @var resource $_socket */
	private $_socket;
	/** @var MSServerSocketProtocol $_protocol */
	private $_protocol;
	/** @var bool $_sendErrorToClient */
	private $_sendErrorToClient;
	/** @var bool $_shutdownOnError */
	private $_shutdownOnError;
	/** @var IMSServerQuery[] $_queries */
	private $_queries;
	/** @var int $_requestTtl */
	private $_requestTtl;
	/** @var IMSServerComponent[] $_workers */
	private $_components;
	/** @var IMSServerRequestHandlerManager $_requestHandlerManager */
	private $_requestHandlerManager;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var MSServerDataParser $_dataParser */
	private $_dataParser;
	/** @var string $_lockFile */
	private $_lockFile;

	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);

	/**
	 * MSServer constructor.
	 *
	 * ATTENTION : Le serializer utilisé doit être le même que celui du KVSContainer !
	 *
	 * @param string $socketPath Chemin vers la socket à créer.
	 * @param MSServerSocketProtocol $protocol               Protocol de communication du serveur.
	 * @param IMSServerEnvironment $MSServerEnvironement     Environnement de travail du serveur.
	 * @param IMSServerRequestHandlerManager $requestHandler Gestionnaire de requêtes du serveur.
	 * @param null|ISerializer $serializer (optionnel defaut : LightSerializer(GZCompressor)) Objet utilisé pour la serialisation et le désérialisaton
	 * @param int    $requestTtl           (optionnel defaut : 60) Temps avant expiration des requêtes.
	 * @param bool   $sendErrorsToClient   (optionnel defaut : true) Force le serveur à envoyer son erreur au client.
	 * @param bool   $shutdownOnError      (optionnel defaut : false) Force le serveur à s'éteindre si une erreur survient.
	 * @param string $errorLogs            (optionnel deffaut : __DIR__."/logs/error_logs.txt") Chemin vers le fichier de logs d'erreur.
	 *
	 * @throws IllegalInvocation
	 */
	public function __construct(
		string $socketPath,
		MSServerSocketProtocol $protocol,
		IMSServerEnvironment $MSServerEnvironement,
		IMSServerRequestHandlerManager $requestHandler,
		?ISerializer $serializer=null,
		int $requestTtl = 60,
		bool $sendErrorsToClient = true,
		bool $shutdownOnError = false,
		string $errorLogs = __DIR__."/logs/error_logs.txt")
	{
		$this->_serializer = $serializer ?? new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
		);
		$this->_dataParser = new MSServerDataParser($this->_serializer);

		//On commence par vérifier l'existence du fichier sempahore permettant d'obtenir le lock
		//Un seul MSServer est autorisé par repertoir de travail.
		$this->_lockFile = $MSServerEnvironement->getWorkingDir().DS."server.lock";
		if(!file_exists($this->_lockFile)){
			touch($this->_lockFile);
		}

		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($this->_lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		$this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(file_exists($socketPath)){
			unlink($socketPath);
		}
		socket_bind($this->_socket,$socketPath);
		socket_listen($this->_socket);

		if($res){
			throw new IllegalInvocation("A MSServer instance is already running for this directory !");
		}else{
			file_put_contents($MSServerEnvironement->getWorkingDir()."/msserver.pid",getmypid());
			$this->_requestHandlerManager = $requestHandler;
			$this->_sendErrorToClient = $sendErrorsToClient;
			$this->_serverKey = new UUID(UUID::V4);
			$this->_environment = $MSServerEnvironement;
			$this->_shutdownOnError = $shutdownOnError;
			$this->_errorLogsFile = $errorLogs;
			$this->_socketAddr = $socketPath;
			$this->_requestTtl = $requestTtl;
			$this->_protocol = $protocol;
			$this->_components = [];
			$this->_queries = [];

			//On initialise et on démarre chaque composant.
			foreach($this->_environment->getComponents() as $k=>$component){
				$component->init(
					$this->_socketAddr,
					$this->_serverKey,
					$requestHandler,
					$this->_serializer,
					$this->_dataParser
				);
				$component->start();
			}
		}
	}

	/**
	 *  Démarre le serveur
	 */
	public function start():void{
		while(true){
			$socket = socket_accept($this->_socket);
			$this->configureSocket($socket);
			$this->process($socket);
		}
	}

	/**
	 *  Procéde à l'execution de la requête.
	 * @param resource $socket Socket connectée
	 */
	public function process($socket){
		try{
			$data = $this->read($socket);
			if(strlen($data)===0){
				throw new MSServerFailure("No data recieved. Server timed out.");
			}

			$parsed = $this->_dataParser->parseData($data);
			$this->_environment->destroyOutdatedSessions();
			$this->cleanOutdatedQueries();

			if($parsed->instanceOf(IMSServerRequest::class)){
				$response = $this->processRequest(
					$socket,
					$parsed,
					$parsed->getSessionId()
				);
				if(!is_null($response)){
					$this->write($socket,$response);
					socket_close($socket);
				}
			}else{
				throw new \InvalidArgumentException("MSServer request have to be instanceof ".IMSServerRequest::class. " but ".$parsed->getClass()." given.");
			}
		}catch(\Exception $e){
			$errorCode = socket_last_error($socket);
			socket_clear_error($socket);

			$this->errorLog(print_r([
				"socket_last_error" => [
					"code" => $errorCode,
					"message" =>socket_strerror($errorCode)
				],
				"error" => (string)$e
			],true));

			if($errorCode === 0){
				if(!($e instanceof MSServerDataParsingFailure)){
					//l'erreur ne provient pas de la déserialisation
					if($this->_sendErrorToClient){
						$this->tryWrite($socket,new RequestError($e));
					}
					socket_close($socket);
					if($this->_shutdownOnError){
						$this->shutdown($e);
					}
				}else{
					$this->tryWrite($socket,new InvalidRequestError($e));
					socket_close($socket);
				}
			}else{
				$this->errorLog(print_r([
					"socket_last_error" => [
						"code" => $errorCode,
						"message" =>socket_strerror($errorCode)
					],
					"error" => (string)$e
				],true));
			}
		}
	}

	/**
	 *  Traite une requête
	 *
	 * @param resource                 $socket Socket demandant l'execution de la requête
	 * @param MSServerDataParserResult $parsed Requête
	 * @param null|string              $sessId (optionnel) Identigiant de session
	 *
	 * @return null|IMSServerResponse
	 * @throws AccessDenied
	 * @throws IllegalInvocation
	 * @throws MSServerFailure
	 * @throws MustBeLogged
	 * @throws NoHandlerForRequest
	 * @throws UnknownRequest
	 * @throws UserNotFound
	 * @throws \Exception
	 */
	private function processRequest(
		$socket,
		MSServerDataParserResult $parsed,
		?string $sessId
	):?IMSServerResponse
	{
		if($parsed->instanceOf(LoginRequest::class)){
			/** @var LoginRequest $request */
			$request = $this->_serializer->unserialize($parsed->getData());
			if($this->_environment->existsUser($request->getLogin())){
				$sessId = $this->_environment->createSessionForUser(
					$request->getLogin(),
					$request->getPassword()
				);
				if(is_null($sessId)){
					throw new AccessDenied(
						"Access denied : cannot find a user matching given informations, or user didn't have enough permissions to access this component."
					);
				}else{
					return new AccessGranted($sessId);
				}
			}else{
				throw new UserNotFound("Unknwown user ".$request->getLogin());
			}
		}else if($parsed->instanceOf(IMSServerComponentResponse::class)){
			//On sait qu'on peut clore la connexion immédiatement.
			socket_close($socket);
			if(isset($this->_queries[$parsed->getQueryId()])){
				$query = $this->_queries[$parsed->getQueryId()];
				$query->getIO()->write($parsed->getData());
				$query->getIO()->closeConnection();
				unset($this->_queries[$parsed->getQueryId()]);
			}
			return null;
		}else if(!is_null($sessId) && $this->_environment->existsUserSession($sessId)){
			$session = $this->_environment->getUserSession($sessId);
			if($parsed->instanceOf(LogoutRequest::class)){
				if($this->_environment->existsUserSession($sessId)){
					$this->_environment->destroyUserSession($sessId);
				}
				return null;
			}else if($parsed->instanceOf(IMSServerAdminRequest::class)){
				if($parsed->instanceOf(ShutdownMSServerRequest::class)){
					socket_close($socket);
					$this->shutdown();
				}else{
					throw new UnknownRequest(
						"Unknwown request : ".get_class($parsed)
					);
				}
			}else if($parsed->instanceOf(IMSServerComponentRequest::class)){
				if($parsed->instanceOf(IClientDeniedRequest::class)){
					//Un client n'a pas le droit de demander directement à un composant de s'éteindre.
					return new RequestError(new AccessDenied(
						"Access denied : you have not enough permissions to perform this action."
					));
				}else{
					$query = new MSServerQuery(
						new MSServerSocketIO($this->_protocol,$socket),
						new MSServerInternalRequest(
							$this->_serverKey,
							$session->getUser()->getName(),
							$parsed->getClass(),
							$parsed->getDataToUnserialize(),
							$parsed->getData()
						),
						microtime(true) + $this->_requestTtl
					);
					$this->_queries[$query->getInternalRequest()->getQueryId()]=$query;
					$hits = $this->_requestHandlerManager->dispatch($query);

					if($hits === 0){
						throw new NoHandlerForRequest("No request handler trigerred this request !");
					}
					return null;
				}
			}
		}else{
			throw new MustBeLogged(
				"Access denied : invalid sessId given. You may have been logged out for inactivity."
			);
		}
		throw new \Exception("Unknown error...");
	}

	/**
	 *  Supprime les requêtes périmées.
	 * @return int Nombre de MSServerQuery périmées
	 */
	private function cleanOutdatedQueries():int{
		$cleaned = 0;
		//On nettoie les requêtes expirées
		foreach($this->_queries as $id=>$query){
			if($query->getExpirationDate()<microtime(true)){
				try{
					$query->getIO()->write($this->_dataParser->lineariseData(new RequestTimeout()));
					$query->getIO()->closeConnection();
				}catch(\Exception $e){}
				unset($this->_queries[$id]);
				$cleaned++;
			}
		}
		return $cleaned;
	}

	/**
	 *  Lis la socket spécifié, sinon la socket principale
	 *
	 * @param resource $socket Socket à lire
	 *
	 * @return string
	 */
	private function read($socket):string{
		return $this->_protocol->read($socket);
	}

	/**
	 *  Tente d'écrire dans une socket. Si l'écriture échoue, l'erreur est inscrite dans le fichier de log d'erreurs.
	 * @param resource                  $socket
	 * @param IMSServerResponse $data
	 *
	 * @return int
	 */
	private function tryWrite($socket,IMSServerResponse $data):int{
		try{
			$this->write($socket,$data);
			return 0;
		}catch(\Exception $e){
			$errorCode = socket_last_error();
			$this->errorLog(print_r([
				"date" => date('l j F Y, H:i:s'),
				"socket_last_error" => [
					"code" => $errorCode,
					"message" =>socket_strerror($errorCode)
				],
				"error" => (string)$e
			],true));
			socket_clear_error();
			return $errorCode;
		}
	}

	/**
	 *  Ecrit des données dans la socket sprécifiée
	 *
	 * @param resource             $socket Socket dans laquelle écrire
	 * @param IMSServerResponse $data   Données à écrire
	 */
	private function write($socket,IMSServerResponse $data):void{
		$this->_protocol->write($socket,$this->_dataParser->lineariseData($data));
	}

	/**
	 * @param \Exception|null $e (optionnel) Erreur à l'origine de l'extinction
	 */
	public function shutdown(\Exception $e = null):void{
		$this->shutdownComponent();
		$this->closeConnections();

		flock($this->_acquiredLockFile,LOCK_UN);
		fclose($this->_acquiredLockFile);
		unlink($this->_lockFile);
		if(file_exists($this->_environment->getWorkingDir()."/msserver.pid"))
			unlink($this->_environment->getWorkingDir()."/msserver.pid");

		if(is_null($e) || $e instanceof ExternalShutdown){
			exit(0);
		}else{
			$this->errorLog((string)$e);
			exit(1);
		}
	}

	/**
	 * @param string $log Log to write
	 */
	private function errorLog(string $log){
		error_log($log.PHP_EOL,3,$this->_errorLogsFile);
	}

	/**
	 *  Ferme la connexion principale du serveur.
	 */
	private function closeConnections():void{
		if(!is_null($this->_socket)){
			socket_close($this->_socket);
			unlink($this->_socketAddr);
		}
	}

	/**
	 *  Donne l'ordre de terminer à tous les workers.
	 */
	private function shutdownComponent():void{
		foreach($this->_environment->getComponents() as $component){
			$component->shutdown();
		}
	}

	/**
	 *  Permet de savoir si le MSServer est déjà en fonctionnement
	 * @return bool
	 */
	public static function isRunning():bool{
		$id = ftok(__FILE__,"A");
		$sem = sem_get($id,1,0666,0);
		$res = !sem_acquire($sem ,true);

		if($res){
			sem_release($sem);
			return false;
		}else{
			return true;
		}
	}

	/**
	 *  Configure une socket
	 * @param resource $socket Socket à configurer
	 */
	private function configureSocket($socket){
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
	}
}