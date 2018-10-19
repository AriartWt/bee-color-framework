<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 06:59
 */

namespace wfw\daemons\kvstore\server;

use wfw\daemons\kvstore\server\containers\ContainerWorker;
use wfw\daemons\kvstore\server\containers\params\client\ContainerWorkerClientParams;
use wfw\daemons\kvstore\server\containers\params\worker\ContainerWorkerParams;
use wfw\daemons\kvstore\server\containers\request\admin\ShutdownContainerWorkerRequest;
use wfw\daemons\kvstore\server\environment\IKVSServerEnvironment;
use wfw\daemons\kvstore\server\errors\AccessDenied;
use wfw\daemons\kvstore\server\errors\ExternalShutdown;
use wfw\daemons\kvstore\server\errors\InactiveKVSContainerWorker;
use wfw\daemons\kvstore\server\errors\KVSServerFailure;
use wfw\daemons\kvstore\server\errors\MustBeLogged;
use wfw\daemons\kvstore\server\requests\IAdminRequest;
use wfw\daemons\kvstore\server\requests\IKVSContainerRequest;
use wfw\daemons\kvstore\server\requests\LoginRequest;
use wfw\daemons\kvstore\server\requests\LogoutRequest;
use wfw\daemons\kvstore\server\requests\IKVSRequest;
use wfw\daemons\kvstore\server\requests\ShutdownKVSServerRequest;
use wfw\daemons\kvstore\server\responses\AccessGranted;
use wfw\daemons\kvstore\server\responses\InternalRequestTimeout;
use wfw\daemons\kvstore\server\responses\InvalidRequestError;
use wfw\daemons\kvstore\server\responses\IKVSContainerResponse;
use wfw\daemons\kvstore\server\responses\IKVSResponse;
use wfw\daemons\kvstore\server\responses\RequestError;
use wfw\daemons\kvstore\server\responses\RequestTimeout;
use wfw\daemons\kvstore\socket\data\errors\KVSDataParsingFailure;
use wfw\daemons\kvstore\socket\data\KVSDataParser;
use wfw\daemons\kvstore\socket\io\KVSSocketIO;
use wfw\daemons\kvstore\socket\protocol\KVSSocketProtocol;

use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Serveur KVS
 */
final class KVSServer
{
	/** @var IKVSServerEnvironment $_environment */
	private $_environment;
	/** @var UUID $_serverKey */
	private $_serverKey;
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var string $_dbPath */
	private $_dbPath;
	/** @var string $_errorLogsFile */
	private $_errorLogsFile;
	/** @var string $_fileLockPath */
	private $_fileLockPath;
	/** @var resource $_acquiredFileLock */
	private $_acquiredFileLock;
	/** @var resource $_socket */
	private $_socket;
	/** @var KVSSocketProtocol $_protocol */
	private $_protocol;
	/** @var bool $_sendErrorToClient */
	private $_sendErrorToClient;
	/** @var bool $_shutdownOnError */
	private $_shutdownOnError;
	/** @var IKVSQuery[] $_queries */
	private $_queries;
	/** @var int $_requestTtl */
	private $_requestTtl;
	/** @var ContainerWorker[] $_workers */
	private $_workers;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var KVSDataParser $_dataParser */
	private $_dataParser;
	/** @var null|string $_lastLinearisation */
	private $_lastLinearisation;

	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);

	/**
	 * KVSServer constructor.
	 *
	 * ATTENTION : le Serializer utilisé doit être le mếme que celui utilisé par la serialsiation !
	 *
	 * @param string  $socketPath Chemin d'accés à la socket unix.
	 * @param string  $dbPath     Chemin d'accés à la base de données.
	 * @param KVSSocketProtocol     $protocol        Protocol de communication à utiliser
	 * @param IKVSServerEnvironment $KVSEnvironement Environnement de travail du serveur.
	 * @param null|ISerializer      $serializer      (optionnel : defaut LightSerializer (GZCompressor)) Objet utilisé pour la serialisation/déserialistation.
	 * @param int    $requestTtl         (optionnel : defaut 60) Temps en secondes avant expiration des requêtes en attente.
	 * @param bool   $sendErrorsToClient (optionnel : defaut true) Envoyer les erreurs au client
	 * @param bool   $shutdownOnError    (optionnel : defaut false) Eteindre le serveur sur erreur
	 * @param string $errorLogs          (optionnel : defaut __DIR__.DS."logs"."error_logs.txt") Fichier de logs d'erreurs.
	 *
	 * @throws IllegalInvocation
	 */
	public function __construct(
		string $socketPath,
		string $dbPath,
		KVSSocketProtocol $protocol,
		IKVSServerEnvironment $KVSEnvironement,
		?ISerializer $serializer =null,
		int $requestTtl = 60,
		bool $sendErrorsToClient = true,
		bool $shutdownOnError = false,
		string $errorLogs = __DIR__."/error_logs.txt"
	)
	{
		$this->_serializer = $serializer ?? new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
		);
		//Comme le parser est unique et s'appuie sur des caractères interdit dans le nom des userName,
		//le parser n'est pas paramètrable.
		$this->_dataParser = new KVSDataParser($this->_serializer);

		$this->_fileLockPath = $lockFile = "$dbPath/kvs.lock";
		if(!file_exists($lockFile)){
			touch($lockFile);
		}
		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredFileLock = $fp;

		if($res){
			throw new IllegalInvocation("An other instance of KVS is already running for this db!");
		}else{
			file_put_contents("$dbPath/kvs.pid",getmypid());
			$this->_serverKey = new UUID(UUID::V4);
			$this->_protocol = $protocol;
			$this->_socketAddr = $socketPath;
			$this->_dbPath = $dbPath;
			$this->_errorLogsFile = $errorLogs;
			$this->_shutdownOnError = $shutdownOnError;
			$this->_sendErrorToClient = $sendErrorsToClient;
			$this->_environment = $KVSEnvironement;
			$this->_queries = [];
			$this->_requestTtl = $requestTtl;

			$this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
			if(file_exists($socketPath)){
				unlink($socketPath);
			}
			socket_bind($this->_socket,$socketPath);
			socket_listen($this->_socket);

			$this->_workers = [];
			foreach($this->_environment->getContainers() as $container){
				$pidFile = $container->getSavePath().DS."pid";
				//Si le KVServer a été arrêté soudainement et que les worker sont toujours en cours d'execution,
				//on les tue
				if(file_exists($pidFile)){
					posix_kill(file_get_contents($pidFile),9);
					unlink($pidFile);
				}
				$worker = new ContainerWorker(
					$this->_socketAddr,
					$this->_protocol,
					$this->_serializer,
					$this->_dataParser,
					new ContainerWorkerParams(
						$container,
						$this->_serverKey,
						$this->_dbPath,
						dirname($this->_socketAddr)
					),
					new ContainerWorkerClientParams(
						$container,
						dirname($this->_socketAddr)
					)
				);
				$this->_workers[$container->getName()] = $worker;
				$worker->start();
			}
		}
	}

	/**
	 *  Démarre le serveur.
	 */
	public function start():void{
		while(true){
			$socket = socket_accept($this->_socket);
			$this->configureSocket($socket);
			$this->process($socket);
		}
	}

	/**
	 *  Traite une connection
	 * @param resource $socket Socket acceptée
	 */
	public function process($socket){
		try{
			$data = $this->read($socket);
			if(strlen($data)===0){
				throw new KVSServerFailure("No data recieved. Socket timed out");
			}
			$parsed = $this->_dataParser->parseData($data);
			$this->_environment->destroyOutdatedSessions();
			$this->cleanOutdatedQueries();

			if($parsed->instanceOf(IKVSRequest::class)){
				if($parsed->instanceOf(LoginRequest::class)){
					$unserialized = $this->_serializer->unserialize($parsed->getData());
					$sessId = $this->_environment->createSessionForUser(
						$unserialized->getContainer(),
						$unserialized->getLogin(),
						$unserialized->getPassword(),
						$unserialized->getDefaultStorageMode()
					);
					if(is_null($sessId)){
						$this->write($socket,new RequestError(
							new AccessDenied(
								"Access denied : cannot find a user matching given informations, or user didn't have enough permissions to access this container."
							)
						));
						socket_close($socket);
					}else{
						$this->write($socket,new AccessGranted($sessId));
						socket_close($socket);
					}
				}else if($parsed->instanceOf(IKVSContainerResponse::class)){
					if(isset($this->_queries[$parsed->getQueryId()])){
						$query = $this->_queries[$parsed->getQueryId()];
						$query->getIO()->write($parsed->getData());
						$query->getIO()->closeConnection();
						unset($this->_queries[$parsed->getQueryId()]);
					}else{
						//Sinon la requête est ignorée
						socket_close($socket);
					}
				}else if($this->_environment->existsUserSession($parsed->getSessionId())){
					if($parsed->instanceOf(LogoutRequest::class)){
						$this->_environment->destroyUserSession($parsed->getSessionId());
						socket_close($socket);
					}else{
						$userSession = $this->_environment->getUserSession($parsed->getSessionId());
						if($parsed->instanceOf(IAdminRequest::class)){
							if($this->_environment->isAdminAccessGranted($userSession->getUser()->getName(),$parsed->getClass())){
								if($parsed->instanceOf(ShutdownKVSServerRequest::class)){
									socket_close($socket);
									$this->shutdown();
								}//Ici ajouter les requêtes d'administration
							}else{
								$this->write($socket,new RequestError(new AccessDenied(
									"Access denied : you didn't have enough rights to perform this action !"
								)));
								socket_close($socket);
							}
						}else{
							if($parsed->instanceOf(IKVSContainerRequest::class)){
								if($parsed->instanceOf(ShutdownContainerWorkerRequest::class)){
									$this->write($socket, new RequestError(new AccessDenied(
										"Illegal shutdown request. A client can't shutdown a worker."
									)));
									socket_close($socket);
								}else{
									$query = new KVSQuery(
										new KVSSocketIO($this->_protocol,$socket),
										new KVSInternalRequest(
											$this->_serverKey,
											$userSession->getUser()->getName(),
											$parsed->getDataToUnserialize(),
											$parsed->getData()
										),
										microtime(true) + $this->_requestTtl
									);

									$this->_queries[$query->getInternalRequest()->getQueryId()]=$query;

									$tries = 0;
									$max_tries = 10;
									$sleep = 0.1 * 1000;

									while($tries<$max_tries && !$this->attemptSendingRequestToWorker($query->getInternalRequest(),$this->_workers[$userSession->getContainer()->getName()])){
										$tries ++;
										$this->_workers[$userSession->getContainer()->getName()]->startWorker();
										usleep($sleep);
									}
									if($tries>=$max_tries){
										$this->_workers[$userSession->getContainer()->getName()]->startWorker();
										//Si on a dépassé le nombre d'essais, et que le worker n'est toujours pas joignable,
										//on envoie l'erreur au client et on pass eà la requête suivante.
										$error = new InternalRequestTimeout($userSession->getContainer()->getName());
										$this->errorLog($error);
										unset($this->_queries[$query->getInternalRequest()->getQueryId()]);
										$query->getIO()->write($this->_dataParser->lineariseData($error));
										$query->getIO()->closeConnection();
									}
								}
							}else{
								//Sinon la requête est ignorée.
								socket_close($socket);
							}
						}
					}
				}else{
					$this->write($socket,new RequestError(
						new MustBeLogged(
							"Access denied : invalid sessId given. You may have been logged out for inactivity."
						)
					));
				}
			}else{
				throw new \InvalidArgumentException("KVSServer requests have to be instanceof ".IKVSRequest::class." but ".$parsed->getClass()." given.");
			}
		}catch(\Exception $e){
			$this->errorLog($e);
			$errorCode = socket_last_error($socket);
			socket_clear_error($socket);

			if($errorCode === 0){
				if(!($e instanceof KVSDataParsingFailure)){
					//l'erreur ne provient pas du format de la requête
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
				$this->errorLog(serialize([
					"socket_last_error" => [
						"code" => $errorCode,
						"message" =>socket_strerror($errorCode)
					],
					"error" => (string)$e
				]));
			}
		}
	}

	/**
	 *  Tente d'envoyer une requête à un worker
	 *
	 * @param IKVSInternalRequest $request
	 * @param ContainerWorker     $worker
	 *
	 * @return bool
	 */
	private function attemptSendingRequestToWorker(IKVSInternalRequest $request,ContainerWorker $worker):bool{
		try{
			$worker->sendQuery($request);
			return true;
		}catch(InactiveKVSContainerWorker $e){
			return false;
		}
	}

	/**
	 *  Supprime les requêtes périmées.
	 * @return int Nombre de KVSQuery périmées
	 */
	private function cleanOutdatedQueries():int{
		$cleaned = 0;
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
	 *
	 * @param                      $socket
	 * @param IKVSResponse $data
	 * @param bool                 $useLastLinearisation
	 *
	 * @return int
	 */
	private function tryWrite($socket,IKVSResponse $data,bool $useLastLinearisation=false):int{
		try{
			$this->write($socket,$data,$useLastLinearisation);
			return 0;
		}catch(\Exception $e){
			$errorCode = socket_last_error();
			$this->errorLog(serialize([
				"date" => date('l j F Y, H:i:s'),
				"socket_last_error" => [
					"code" => $errorCode,
					"message" =>socket_strerror($errorCode)
				],
				"error" => (string)$e
			]));
			socket_clear_error();
			return $errorCode;
		}
	}

	/**
	 *  Ecrit des données dans la socket sprécifiée
	 *
	 * @param resource             $socket  Socket dans laquelle écrire
	 * @param IKVSResponse $data    Données à écrire
	 * @param bool                 $useLast Utilise le résultat de la derniere linéarisation.
	 */
	private function write($socket,IKVSResponse $data,bool $useLast=false):void{
		if(!$useLast || is_null($this->_lastLinearisation)){
			$this->_lastLinearisation = $this->_dataParser->lineariseData($data);
		}
		$this->_protocol->write($socket,$this->_lastLinearisation);
	}

	/**
	 * @param \Exception|null $e (optionnel) Erreur à l'origine de l'extinction
	 */
	public function shutdown(\Exception $e = null):void{
		$this->shutdownContainers();
		$this->closeConnections();

		flock($this->_acquiredFileLock,LOCK_UN);
		unlink($this->_fileLockPath);

		if(file_exists("$this->_dbPath/kvs.pid"))
			unlink("$this->_dbPath/kvs.pid");

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
	private function shutdownContainers():void{
		foreach($this->_workers as $worker){
			$worker->shutdown($this->_serverKey);
		}
	}

	/**
	 *  Permet de savoir si le KVS est déjà en fonctionnement
	 *
	 * @param string $dbPath
	 * @return bool
	 */
	public static function isRunning(string $dbPath):bool{
		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen("$dbPath/kvs.lock","r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);

		if($res){
			$res = false;
			flock($fp,LOCK_UN);
		}else{
			$res = true;
		}
		fclose($fp);

		return $res;
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