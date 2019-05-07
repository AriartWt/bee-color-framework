<?php
namespace wfw\daemons\kvstore\server\containers;

use wfw\daemons\kvstore\server\containers\data\IKVSContainerDataManager;
use wfw\daemons\kvstore\server\containers\data\KVSContainerDataManager;
use wfw\daemons\kvstore\server\containers\data\KVSStorageModeInflector;
use wfw\daemons\kvstore\server\containers\errors\KVSContainerFailure;
use wfw\daemons\kvstore\server\containers\params\client\ContainerWorkerClientParams;
use wfw\daemons\kvstore\server\containers\params\worker\ContainerWorkerParams;
use wfw\daemons\kvstore\server\containers\request\admin\IKVSAdminContainerRequest;
use wfw\daemons\kvstore\server\containers\request\admin\PurgeContainerRequest;
use wfw\daemons\kvstore\server\containers\request\admin\ShutdownContainerWorkerRequest;
use wfw\daemons\kvstore\server\containers\request\read\ExistsKeyRequest;
use wfw\daemons\kvstore\server\containers\request\read\GetKeyRequest;
use wfw\daemons\kvstore\server\containers\request\read\IKVSReadContainerRequest;
use wfw\daemons\kvstore\server\containers\request\write\ChangeStorageModeRequest;
use wfw\daemons\kvstore\server\containers\request\write\IKVSWriteContainerRequest;
use wfw\daemons\kvstore\server\containers\request\write\RemoveRequest;
use wfw\daemons\kvstore\server\containers\request\write\SetRequest;
use wfw\daemons\kvstore\server\containers\request\write\SetTtlRequest;
use wfw\daemons\kvstore\server\containers\request\write\TouchRequest;
use wfw\daemons\kvstore\server\containers\response\ContainerResponse;
use wfw\daemons\kvstore\server\containers\response\DoneResponse;
use wfw\daemons\kvstore\server\containers\response\ExistKeyResponse;
use wfw\daemons\kvstore\server\containers\response\GetKeyResponse;
use wfw\daemons\kvstore\server\environment\IKVSContainer;
use wfw\daemons\kvstore\server\environment\KVSUserPermissions;
use wfw\daemons\kvstore\server\errors\AccessDenied;
use wfw\daemons\kvstore\server\errors\InactiveKVSContainerWorker;
use wfw\daemons\kvstore\server\IKVSInternalRequest;
use wfw\daemons\kvstore\server\KVSInternalRequest;
use wfw\daemons\kvstore\server\requests\IKVSContainerRequest;
use wfw\daemons\kvstore\server\responses\IKVSContainerResponse;
use wfw\daemons\kvstore\server\responses\RequestError;
use wfw\daemons\kvstore\socket\data\KVSDataParser;
use wfw\daemons\kvstore\socket\data\KVSDataParserResult;
use wfw\daemons\multiProcWorker\Worker;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\Type;

/**
 * Worker s'occupant d'un container
 */
final class ContainerWorker extends Worker {
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var null|ContainerWorkerParams $_workerParams */
	private $_workerParams;
	/** @var null|ContainerWorkerClientParams $_clientParams */
	private $_clientParams;
	/** @var resource $_acquiredRunningSem */
	private $_acquiredLockFile;
	/** @var string $_pidFilePath */
	private $_pidFilePath;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var KVSDataParser $_dataParser */
	private $_dataParser;

	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);

	/**
	 * ContainerWorker constructor.
	 *
	 * @param string                           $socket_addr  Adresse de la socket du KVSServer (pour les réponses)
	 * @param ISocketProtocol                  $protocol     Protocol de communication pour les échanges KVS/worker
	 * @param ISerializer                      $serializer   Objet utilisé pour la sérialisation/déserialsiation
	 * @param KVSDataParser                    $dataParser   Parseur de requêtes
	 * @param null|ContainerWorkerParams       $workerParams Paramètres du worker.
	 * @param null|ContainerWorkerClientParams $clientParams Paramètres du client.
	 */
	public function __construct(
		string $socket_addr,
		ISocketProtocol $protocol,
		ISerializer $serializer,
		KVSDataParser $dataParser,
		?ContainerWorkerParams $workerParams,
		?ContainerWorkerClientParams $clientParams=null
	){
		$this->_serializer = $serializer;
		$this->_dataParser = $dataParser;
		$this->_socketAddr = $socket_addr;
		if(!is_null($clientParams)){
			$this->_clientParams = $clientParams;
		}
		if(!is_null($workerParams)){
			$this->_workerParams = $workerParams;
			$this->_pidFilePath = $this->_workerParams->getContainer()->getSavePath()."/pid";
			$errorLogFile = $this->_workerParams->getContainer()->getSavePath()."/error_logs.txt";
		}else{
			$this->_pidFilePath = $this->_clientParams->getContainer()->getSavePath()."/pid";
			$errorLogFile = $this->_clientParams->getContainer()->getSavePath()."/error_logs.txt";
		}

		parent::__construct($protocol,$errorLogFile,true,false);
	}

	/**
	 * Lancé seulement par le worker
	 */
	protected function runWorker(): void {
		$lockFile = self::getContainerLockFile($this->_workerParams->getContainer());
		if(!file_exists($lockFile)){
			touch($lockFile);
		}
		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		if($res) throw new IllegalInvocation(
			"A worker is already running for the container ".$this->_workerParams->getContainer()->getName()
		);
		cli_set_process_title("WFW KVS ".$this->_workerParams->getContainer()->getName()." instance");
		//On écrit le PID dans un fichier.
		file_put_contents($this->getPidFilePath(),getmypid());

		$socketPath = $this->getWorkerSocketAddr();
		$this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(file_exists($socketPath)){
			unlink($socketPath);
		}
		socket_bind($this->_socket,$socketPath);
		socket_listen($this->_socket);

		//On crée le DataManager
		$dataManager = new KVSContainerDataManager(
			$this->_workerParams->getContainer(),
			new KVSStorageModeInflector(
				$this->_workerParams->getContainer()->getSavePath()
			),
			$this->_serializer
		);

		$continue = true;
		while($continue){
			$accepted = socket_accept($this->_socket);
			$this->configureSocket($accepted);
			$continue = $this->process($accepted,$dataManager);
		}
		try{
			$this->shutdown();
		}catch(\Exception $e) {
			$this->errorLog(print_r($e, true));
		}
	}

	/**
	 * Lancé seulement par le client
	 */
	protected function runClient(): void {}

	/**
	 * @return string
	 */
	public function getPidFilePath():string{
		return $this->_pidFilePath;
	}

	/**
	 *  Retourne le chemin d'accés au fichier .lock permettant de garantir qu'un seul ContainerWorker
	 *        existe par container.
	 * @param IKVSContainer $container Container
	 * @return string
	 */
	private static function getContainerLockFile(IKVSContainer $container):string{
		return $container->getSavePath()."/".$container->getName().".lock";
	}

	/**
	 * Traite une connexion sur une socket.
	 *
	 * @param resource                         $socket      Socket
	 * @param IKVSContainerDataManager $dataManager Gestionnaire de données
	 *
	 * @return bool False pour interrompre le worker
	 * @throws IllegalInvocation
	 */
	public function process($socket,IKVSContainerDataManager $dataManager):bool{
		$unserializeError = true;
		$request = $this->read($socket);
		socket_close($socket);
		try{
			if(strlen($request)===0){
				throw new KVSContainerFailure("No data recieved. Socket timed out.");
			}
			$request = $this->_dataParser->parseData($request);
			$unserializeError = false;
			if($request->instanceOf(IKVSInternalRequest::class)){
				if($this->_workerParams->matchServerKey($request->getServerKey())){
					return $this->handle(
						$dataManager,
						$this->_serializer->unserialize($request->getDataToUnserialize()),
						$request);
				}else{
					throw new KVSContainerFailure("Wrong server key given.");
				}
			}else{
				throw new KVSContainerFailure(
					"Unreadable request ! (requests must be instanceof "
					.IKVSInternalRequest::class." but ".(new Type($request))->get()." given !)"
				);
			}
		}catch(\Exception $e){
			//Toute requête avec erreur de serialisation est ignorée
			if(!$unserializeError){
				//Toute requête qui n'est pas un objet de type IKVSInterfnalRequest est ignoré.
				if($request->instanceOf(IKVSInternalRequest::class)){
					//Toute requête dont la clé serveur est fausse est ignorée
					if($this->_workerParams->matchServerKey($request->getServerKey())){
						//Sinon on transmet l'erreur au KVSServeur.
						$this->sendResponse(new ContainerResponse(
							$request->getQueryId(),
							new RequestError($e))
						);
					}
				}
			}
		}
		return true;
	}

	/**
	 * Traite une requête
	 *
	 * @param IKVSContainerDataManager $dataManager
	 * @param IKVSContainerRequest     $clientRequest
	 * @param KVSDataParserResult              $request
	 *
	 * @return bool False s'il faut arrêter le worker
	 * @throws IllegalInvocation
	 * @throws KVSContainerFailure
	 */
	public function handle(
		IKVSContainerDataManager $dataManager,
		IKVSContainerRequest $clientRequest,
		KVSDataParserResult $request
	):bool {
		if($clientRequest instanceof IKVSAdminContainerRequest){
			if($clientRequest instanceof ShutdownContainerWorkerRequest){
				return false;
			}else if($clientRequest instanceof PurgeContainerRequest){
				if($this->_workerParams->getContainer()->isUserAccessGranted($request->getUserName(),KVSUserPermissions::ADMIN)){
					$dataManager->purge();
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else{
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new RequestError(new AccessDenied(
							"You don't have enough rights to perform an admin request on this container !"
						))
					));
				}
			}else{
				throw new KVSContainerFailure("Unknown request : ".$request->getClass());
			}
		}else if($clientRequest instanceof IKVSReadContainerRequest){
			if($this->_workerParams->getContainer()->isUserAccessGranted($request->getUserName(),KVSUserPermissions::READ)){
				if($clientRequest instanceof ExistsKeyRequest){
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new ExistKeyResponse($dataManager->exists($clientRequest->getKey()))
					));
				}else if($clientRequest instanceof GetKeyRequest){
					$data = $dataManager->get($clientRequest->getKey())??"";
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new GetKeyResponse($data)
					));
				}else{
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new RequestError(new KVSContainerFailure(
							"Unknown request : ".$request->getClass()
						))
					));
				}
			}else{
				$this->sendResponse(new ContainerResponse(
					$request->getQueryId(),
					new RequestError(new AccessDenied(
						"You don't have enough rigths to perform a read request on this container !"
					))
				));
			}
		}else if($clientRequest instanceof IKVSWriteContainerRequest){
			if($this->_workerParams->getContainer()->isUserAccessGranted(
				$request->getUserName(),
				KVSUserPermissions::WRITE
			)){
				$sendDone = true;
				if($clientRequest instanceof SetRequest){
					$dataManager->set(
						$clientRequest->getKey(),
						$request->getData(),
						$clientRequest->getTtl(),
						$clientRequest->getStorageMode()
					);
				}else if($clientRequest instanceof RemoveRequest){
					$dataManager->remove($clientRequest->getKey());
				}else if($clientRequest instanceof SetTtlRequest){
					$dataManager->setTtl(
						$clientRequest->getKey(),
						$clientRequest->getTtl()
					);
				}else if($clientRequest instanceof ChangeStorageModeRequest){
					$dataManager->changeStorageMode(
						$clientRequest->getKey(),
						$clientRequest->getStorageMode()
					);
				}else if($clientRequest instanceof TouchRequest){
					$dataManager->touch(
						$clientRequest->getKey(),
						$clientRequest->getTtl()
					);
				}else{
					$sendDone = false;
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new RequestError(new KVSContainerFailure(
							"Unknown request : ".get_class($clientRequest)
						))
					));
				}
				if($sendDone){
					$this->sendResponse(new ContainerResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}
			}else{
				$this->sendResponse(new ContainerResponse(
					$request->getQueryId(),
					new RequestError(new AccessDenied(
						"You don't have enough rigths to perform a write request on this container !"
					))
				));
			}
		}else{
			throw new KVSContainerFailure("Unknown request : ".get_class($clientRequest));
		}
		return true;
	}

	/**
	 * @return string Chemin d'accés à la socket du worker
	 */
	private function getWorkerSocketAddr():string{
		$socketEndName = ".socket";
		if($this->getWorkerMode() === self::WORKER_MODE){
			return $this->_workerParams->getSocketDir().DS.$this->_workerParams->getContainer()->getName().$socketEndName;
		}else{
			return $this->_clientParams->getSocketDir().DS.$this->_clientParams->getContainer()->getName().$socketEndName;
		}
	}

	/**
	 *  WORKER_MODE : éteint proprement le worker
	 *  CLIENT_MODE : envoie la commande d'extinction au worker
	 *
	 * @param null|string $serverKey
	 *
	 * @throws IllegalInvocation
	 * @throws InactiveKVSContainerWorker
	 */
	public function shutdown(?string $serverKey=null):void{
		if($this->getWorkerMode() === self::WORKER_MODE){
			if(is_resource($this->_socket)){
				socket_close($this->_socket);
				$this->_socket = null;
			}

			if(file_exists($this->getWorkerSocketAddr())){
				unlink($this->getWorkerSocketAddr());
			}
			if(file_exists($this->_pidFilePath)){
				unlink($this->_pidFilePath);
			}

			flock($this->_acquiredLockFile,LOCK_UN);
			fclose($this->_acquiredLockFile);

			exit(0);
		}else $this->sendQuery(new KVSInternalRequest(
			$serverKey,
			"",
			$this->_serializer->serialize(new ShutdownContainerWorkerRequest()))
		);
	}

	/**
	 *  Envoie une requête au worker
	 *
	 * @param IKVSInternalRequest $request Requête à envoyer
	 *
	 * @throws IllegalInvocation
	 * @throws InactiveKVSContainerWorker
	 */
	public function sendQuery(IKVSInternalRequest $request):void{
		if($this->getWorkerMode() === self::CLIENT_MODE){
			try{
				$socket = $this->createClientSocket($this->getWorkerSocketAddr());
				$this->write($this->_dataParser->lineariseData($request),$socket);
				socket_close($socket);
			}catch(\Exception $e){
				throw new InactiveKVSContainerWorker("Unable to query ".$this->_clientParams->getContainer()->getName()." container's worker : ".$e->getMessage());
			}
		}else{
			throw new IllegalInvocation("Cannot use sendQuery in WORKER_MODE");
		}
	}

	/**
	 *  Envoie une réponse vers le KVSServeur
	 *
	 * @param IKVSContainerResponse $response Réponse à transmettre
	 *
	 * @throws IllegalInvocation
	 */
	public function sendResponse(IKVSContainerResponse $response):void{
		if($this->getWorkerMode() === self::WORKER_MODE){
			$socket = $this->createClientSocket($this->_socketAddr);
			try{
				$this->write($this->_dataParser->lineariseData($response),$socket);
			}catch(\Exception $e){
				$socketError = socket_last_error();
				$this->errorLog(print_r([
						"socket" => [
							"code" => $socketError,
							"str" => socket_strerror($socketError)
						],
						"Exception" => $e
					],true)
				);
			}
		}else{
			throw new IllegalInvocation("Cannot use sendResponse in CLIENT_MODE");
		}
	}

	/**
	 *  Crée une socket et la paramètre avec le timeout défini par $this->_socketTiemout
	 * @param string $addr
	 *
	 * @return resource
	 */
	private function createClientSocket(string $addr){
		$socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
		socket_connect($socket,$addr);
		return $socket;
	}
}