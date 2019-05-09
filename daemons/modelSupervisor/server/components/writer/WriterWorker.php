<?php
namespace wfw\daemons\modelSupervisor\server\components\writer;

use wfw\daemons\modelSupervisor\server\components\errors\MSServerComponentFailure;
use wfw\daemons\modelSupervisor\server\components\responses\IMSServerComponentResponse;
use wfw\daemons\modelSupervisor\server\components\writer\errors\WriterComponentFailure;
use wfw\daemons\modelSupervisor\server\components\writer\params\worker\WriterComponentWorkerParams;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\IWriterAdminRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RebuildAllModels;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RebuildModels;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\RemoveIndex;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\SaveDoneRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\SetIndex;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\ShutdownWriterRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\TriggerSaveRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\admin\UpdateSnapshot;
use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\read\IWriterReadRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\read\QueryModel;
use wfw\daemons\modelSupervisor\server\components\writer\requests\write\ApplyEvents;
use wfw\daemons\modelSupervisor\server\components\writer\requests\write\IWriterWriteRequest;
use wfw\daemons\modelSupervisor\server\components\writer\requests\write\SaveChangedModels;
use wfw\daemons\modelSupervisor\server\components\writer\responses\QueryModelResponse;
use wfw\daemons\modelSupervisor\server\components\writer\responses\WriterResponse;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponentEnvironment;
use wfw\daemons\modelSupervisor\server\environment\MSServerUserPermissions;
use wfw\daemons\modelSupervisor\server\errors\AccessDenied;
use wfw\daemons\modelSupervisor\server\IMSServerInternalRequest;
use wfw\daemons\modelSupervisor\server\MSServerInternalRequest;
use wfw\daemons\modelSupervisor\server\responses\DoneResponse;
use wfw\daemons\modelSupervisor\server\responses\InternalRequestTimeout;
use wfw\daemons\modelSupervisor\server\responses\RequestError;
use wfw\daemons\modelSupervisor\socket\data\MSServerDataParserResult;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;
use wfw\daemons\multiProcWorker\Worker;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\data\IDataParser;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\PHPString;
use wfw\engine\lib\PHP\types\Type;

/**
 * Writer Worker
 */
final class WriterWorker extends Worker {
	/** @var array $_saveRequests */
	private $_saveRequests;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var IDataParser $_dataParser */
	private $_dataParser;
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var string $_workerSocket */
	private $_workerSocket;
	/** @var IMSServerComponentEnvironment $_environment */
	private $_environment;
	/** @var string $_pidFilePath */
	private $_pidFilePath;
	/** @var int $_maxAttempts */
	private $_maxAttempts;
	/** @var float $_delay */
	private $_delay;
	/** @var float $_saveFrequency */
	private $_saveFrequency;
	/** @var null|WriterComponentWorkerParams $_workerParams */
	private $_workerParams;
	/** @var $_lockFile */
	private $_lockFile;
	/** @var resource $_acquiredLockFile */
	private $_acquiredLockFile;
	/** @var \Exception|null $_lastAttemptError */
	private $_lastAttemptError;
	/** @var int $_periodicSavePID */
	private $_periodicSavePID;

	/**
	 * WriterWorker2 constructor.
	 *
	 * @param string                           $socket_addr
	 * @param array                            $params
	 * @param string                           $socket_dir
	 * @param ISerializer                      $serializer
	 * @param IDataParser                      $dataParser
	 * @param IMSServerComponentEnvironment    $environment
	 * @param null|WriterComponentWorkerParams $workerParams
	 */
	public function __construct(
		string $socket_addr,
		array $params = [],
		string $socket_dir,
		ISerializer $serializer,
		IDataParser $dataParser,
		IMSServerComponentEnvironment $environment,
		?WriterComponentWorkerParams $workerParams=null
	){
		$this->_saveRequests = [];
		$this->_serializer = $serializer;
		$this->_dataParser = $dataParser;
		$this->_environment = $environment;

		//On parse le chemin d'accés au fichier de logs d'erreurs
		$errorLog = new PHPString(
			$environment->getString("error_logs")
			?? "error_logs.txt"
		);
		if(!$errorLog->startBy("/")){
			$errorLog = $environment->getWorkingDir().DS.$errorLog;
		}else{
			$errorLog = (string) $errorLog;
		}
		parent::__construct(
			$params["protocol"]??new MSServerSocketProtocol(),
			$errorLog,
			true,
			false
		);

		$this->_workerSocket = $environment->getWorkingDir()."/writer.socket";
		$this->_socketAddr = $socket_addr;
		$this->_environment = $environment;
		$this->_pidFilePath = $environment->getWorkingDir()."/writer.pid";

		$this->_maxAttempts = $this->_environment->getInt("max_attempts")??10;
		$this->_maxAttempts = (($this->_maxAttempts > 0) ? $this->_maxAttempts : 10);

		$this->_delay = $this->_environment->getFloat("delay")??0.1;
		$this->_delay = (($this->_delay>0) ? $this->_delay : 0.1) * 1000;

		$this->_saveFrequency = $this->_environment->getFloat("save_frequency")??60;
		$this->_saveFrequency = (($this->_saveFrequency>0) ? $this->_saveFrequency : 60) * 1000000;

		$this->_workerParams = $workerParams;
	}

	/**
	 * Lancé seulement par le worker
	 */
	protected function runWorker(): void {
		//On commence par vérifier l'existence du fichier lock permettant d'obtenir le lock
		//Un seul writer par MSServer
		$this->_lockFile = $this->_environment->getWorkingDir()."/writer.lock";
		if(!file_exists($this->_lockFile)){
			touch($this->_lockFile);
		}

		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($this->_lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		if($res)throw new IllegalInvocation("A writer instance is already running for this MSServer !");

		file_put_contents(
			$this->_environment->getWorkingDir()."/writer.pid",
			$this->getWorkerPid()
		);

		$socketPath = $this->getWorkerSocketAddr();
		$this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(file_exists($socketPath)){
			unlink($socketPath);
		}
		socket_bind($this->_socket,$socketPath);
		socket_listen($this->_socket);
		cli_set_process_title(cli_get_process_title()." Writer");
		$this->startPeriodicSaves();

		$continue = true;
		$this->_environment->getLogger()->log(
			"[WRITER] [WORKER] Worker started.",
			ILogger::LOG
		);
		while($continue){
			$accepted = socket_accept($this->_socket);
			$this->configureSocket($accepted);
			$this->_environment->getLogger()->log(
				"[WRITER] [WORKER] New incoming connection accepted.",
				ILogger::LOG
			);
			$continue = $this->process($accepted);
			$this->_environment->getLogger()->log(
				"[WRITER] [WORKER] Query successfully processed.",
				ILogger::LOG
			);
			$this->cleanZombies();
		}

		$this->shutdown();
	}

	/**
	 *  Vérifie si un worker est démarré pour le container spécifié.
	 *
	 * @param IMSServerComponentEnvironment $env Environnement du writer
	 *
	 * @return bool
	 */
	public static function isRunning(IMSServerComponentEnvironment $env):bool{
		//On vérifie qu'on peut acquérir le lock.
		$file = self::getLockFile($env);
		if(!file_exists($file)) return false;

		$fp = fopen($file,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		if(!$res) flock($fp,LOCK_UN);

		fclose($fp);
		return $res;
	}

	/**
	 * @param IMSServerComponentEnvironment $env Environnement du writer
	 * @return string
	 */
	private static function getLockFile(IMSServerComponentEnvironment $env):string{
		return $env->getWorkingDir()."/writer.lock";
	}

	/**
	 * Lancé seulement par le client
	 */
	protected function runClient(): void {}

	/**
	 * Traite une connection sur une socket
	 *
	 * @param resource $socket Socket acceptée
	 *
	 * @return bool False s'il faut arrêter le worker.
	 */
	private function process($socket):bool{
		$unserializeError = true;
		$request = $this->read($socket);

		try{
			socket_close($socket);
			if(strlen($request)===0){
				throw new WriterComponentFailure("No data recieved. Socket timed out.");
			}
			/** @var MSServerDataParserResult $request */
			$request = $this->_dataParser->parseData($request);
			$unserializeError = false;

			if($request->instanceOf(IMSServerInternalRequest::class)){
				if($this->_workerParams->matchServerKey($request->getServerKey())){
					return $this->handle(
						$this->_serializer->unserialize(
							$request->getDataToUnserialize()
						),
						$request
					);
				}else{
					throw new WriterComponentFailure("Wrong server key given.");
				}
			}else{
				throw new WriterComponentFailure(
					"Unreadbale request ! (requests must be instanceof "
					.IMSServerInternalRequest::class." but ".(new Type($request))->get()." given !)"
				);
			}
		}catch(\Exception $e){
			$tags = "[WRITER] [WORKER] [REQUEST ERROR]";
			//Toute requête avec erreur de serialisation est ignorée
			if(!$unserializeError){
				//Toute requête qui n'est pas un objet de type IMSServerInternalRequest est ignoré.
				if($request->instanceOf(IMSServerInternalRequest::class)){
					//Toute requête dont la clé serveur est fausse est ignorée
					if($this->_workerParams->matchServerKey($request->getServerKey())){
						try{
							//Sinon on transmet l'erreur au MSServer.
							$this->sendResponse(new WriterResponse(
								$request->getQueryId(),
								new RequestError($e)
							));
							$this->_environment->getLogger()->log(
								"$tags Report successfully sent to client",
								ILogger::LOG
							);
						}catch(\Exception | \Error $e){
							$this->_environment->getLogger()->log(
								"$tags Unable to send error to client : $e",
								ILogger::ERR
							);
						}
					}else $this->_environment->getLogger()->log(
						"$tags Client request ignored : wrong server key given.",
						ILogger::WARN
					);
				}else $this->_environment->getLogger()->log(
					"$tags Client request ignored : recieved object is a ".$request->getClass()
					." instead of ".IMSServerInternalRequest::class,ILogger::WARN

				);
			}else $this->_environment->getLogger()->log(
				"$tags Client's request ignored : recieved data can't be unserialized.",
				ILogger::WARN
			);
		}
		return true;
	}

	/**
	 * Traite les requêtes faites au writer.
	 *
	 * @param IWriterRequest           $clientRequest Requête du client
	 * @param MSServerDataParserResult $request       Requête du MSServer
	 *
	 * @return bool False s'il faut arrêter le worker
	 * @throws IllegalInvocation
	 * @throws MSServerComponentFailure
	 * @throws WriterComponentFailure
	 */
	private function handle(IWriterRequest $clientRequest, MSServerDataParserResult $request):bool{
		$this->_environment->getLogger()->log(
			"[WRITER] [WORKER] Processing ".get_class($clientRequest)." (query id : "
			.$request->getQueryId().")...",
			ILogger::LOG
		);
		if($clientRequest instanceof IWriterAdminRequest){
			return $this->handleAdminRequest($clientRequest,$request);
		}else if($clientRequest instanceof IWriterReadRequest){
			return $this->handleReadRequest($clientRequest,$request);
		}else if($clientRequest instanceof IWriterWriteRequest){
			return $this->handleWriteRequest($clientRequest,$request);
		}else{
			throw new WriterComponentFailure("Unknown request : ".get_class($clientRequest));
		}
	}

	/**
	 * Traite une requête d'administration
	 *
	 * @param IWriterAdminRequest      $clientRequest Requête du client
	 * @param MSServerDataParserResult $request       Requête du MSServer
	 *
	 * @return bool False s'il faut arrêter le worker
	 * @throws IllegalInvocation
	 * @throws MSServerComponentFailure
	 */
	private function handleAdminRequest(
		IWriterAdminRequest $clientRequest,
		MSServerDataParserResult $request
	):bool{
		if($clientRequest instanceof ShutdownWriterRequest){
			if($this->_workerParams->getModelManager()->needASave()){
				$this->_workerParams->getModelManager()->reset(
					$this->triggerSave(true)
				);
			}
			return false;
		}else if($clientRequest instanceof TriggerSaveRequest){
			if($clientRequest->getPID() === $this->_periodicSavePID){
				if($this->_workerParams->getModelManager()->needASave()){
					$this->forkAndSave($clientRequest);
				}
			}else{
				//On tue le periodic saver s'il ne correspond pas à celui que
				//le MSServer courant à mis en place par fork().
				//Permet d'éviter qu'un processus reliquat d'un autre MSServer interfère dans
				//les ordres de sauvegarde
				posix_kill($clientRequest->getPID(),9);
			}
		}else if($clientRequest instanceof SaveDoneRequest){
			if(isset($this->_saveRequests[$clientRequest->getSaveId()])){
				unset($this->_saveRequests[$clientRequest->getSaveId()]);
				$this->_workerParams->getModelManager()->reset(
					$clientRequest->getSavedModels()
				);
			}
		}else{
			if($this->_environment->isUserAccessGranted(
				$request->getUserName(),MSServerUserPermissions::ADMIN)){
				if($clientRequest instanceof SetIndex){
					$this->_workerParams->getModelManager()->setIndex(
						$clientRequest->getModelName(),
						$clientRequest->getName(),
						$clientRequest->getSpec(),
						$clientRequest->isModifyIfExists()
					);
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else if($clientRequest instanceof RemoveIndex){
					$this->_workerParams->getModelManager()->removeIndex(
						$clientRequest->getModelName(),
						$clientRequest->getName()
					);
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else if($clientRequest instanceof RebuildModels){
					$this->_workerParams->getModelSynchronizer()->getModelSnapshoter()->updateSnapshot(
						...$clientRequest->getModels()
					);
					$this->_workerParams->getModelSynchronizer()->synchronize();
					$this->_workerParams->getModelManager()->reloadModels();
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else if($clientRequest instanceof RebuildAllModels){
					$this->_workerParams->getModelSynchronizer()->getModelSnapshoter()->rebuildSnapshot();
					$this->_workerParams->getModelSynchronizer()->synchronize();
					$this->_workerParams->getModelManager()->reloadModels();
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else if($clientRequest instanceof UpdateSnapshot){
					$this->_workerParams->getModelSynchronizer()->synchronize();
					$this->_workerParams->getModelManager()->reloadModels();
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new DoneResponse()
					));
				}else{
					$this->sendResponse(new WriterResponse(
						$request->getQueryId(),
						new RequestError(new WriterComponentFailure(
							"Unknown request : ".get_class($clientRequest)
						))
					));
				}
			}else{
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new RequestError(new AccessDenied(
						"Access denied : you don't have enough permissions to perform "
						."an admin request."
					))
				));
			}
		}
		return true;
	}

	/**
	 * Traite une requête de lecture
	 *
	 * @param IWriterReadRequest       $clientRequest Requête du client
	 * @param MSServerDataParserResult $request       Requête du MSServer
	 *
	 * @return bool False s'il faut arrêter le worker
	 * @throws IllegalInvocation
	 */
	private function handleReadRequest(
		IWriterReadRequest $clientRequest,
		MSServerDataParserResult $request
	):bool{
		if($this->_environment->isUserAccessGranted(
			$request->getUserName(),
			MSServerUserPermissions::READ))
		{
			if($clientRequest instanceof QueryModel){
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new QueryModelResponse($this->_serializer->serialize(
						$this->_workerParams->getModelManager()->query(
							$clientRequest->getModelName(),
							$request->getData()
						)
					))
				));
			}else{
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new RequestError(new WriterComponentFailure(
						"Unknown request : ".get_class($clientRequest)
					))
				));
			}
		}else{
			$this->sendResponse(new WriterResponse(
				$request->getQueryId(),
				new RequestError(new AccessDenied(
					"Access denied : user '".$request->getUserName()
					."' don't have enough permissions to perform a read request."
				))
			));
		}
		return true;
	}

	/**
	 * Traite les requêtes d'écriture
	 *
	 * @param IWriterWriteRequest      $clientRequest Requ$ete du client
	 * @param MSServerDataParserResult $request       Requête du MSServer
	 *
	 * @return bool
	 * @throws IllegalInvocation
	 */
	private function handleWriteRequest(
		IWriterWriteRequest $clientRequest,
		MSServerDataParserResult $request
	):bool{
		if($this->_environment->isUserAccessGranted(
			$request->getUserName(),
			MSServerUserPermissions::WRITE))
		{
			if($clientRequest instanceof ApplyEvents){
				/** @var \wfw\engine\core\domain\events\EventList $events */
				$events = $this->_serializer->unserialize($request->getData());
				$this->_workerParams->getModelManager()->dispatch($events);
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new DoneResponse()
				));
			}else if($clientRequest instanceof SaveChangedModels){
				if($this->_workerParams->getModelManager()->needASave()){
					$this->_workerParams->getModelManager()->reset(
						$this->triggerSave(true)
					);
				}
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new DoneResponse())
				);
			}else{
				$this->sendResponse(new WriterResponse(
					$request->getQueryId(),
					new RequestError(new WriterComponentFailure(
						"Unknown request : ".get_class($clientRequest)
					))
				));
			}
		}else{
			$this->sendResponse(new WriterResponse(
				$request->getQueryId(),
				new RequestError(new AccessDenied(
					"Access denied : user '".$request->getUserName()
					."' don't have enough permissions to perform a write request."
				))
			));
		}
		return true;
	}

	/**
	 * Permet de supprimer les processus zombies issus des forks de sauvegarde.
	 */
	private function cleanZombies():void{
		$pid = 1;
		$status=null;
		while($pid>0){
			$pid = pcntl_waitpid(-1,$status,WNOHANG);
			$nb = 0;
			foreach($this->_saveRequests as $k=>$request){
				if($pid === $request["pid"]){
					unset($this->_saveRequests[$k]);
					$nb++;
				}
			}
			if($nb > 0) $this->_environment->getLogger()->log(
				"[WRITER] [WORKER] Zombie processes with waitting $nb save requests detected (pid : $pid). All requests have been cleaned up.",
				ILogger::WARN
			);
		}
	}

	/**
	 * Fork le processus courant. Dans le fils, aussi longtemps que le père existe,
	 * on envoie une requête au serveur pour demander une sauvegarde de manière periodique
	 * en envoyant au WriterComponentWorker une requête de type TriggerSaveRequest.
	 */
	private function startPeriodicSaves(){
		if($this->getWorkerMode() === self::WORKER_MODE){
			//on fork pour envoyer des requêtes de sauvegarde à intervals réguliers
			//sur le worker.
			$pid = pcntl_fork();
			$tags = "[WRITER] [WORKER] [PERIODIC SAVER]";
			if ($pid === -1){
				$this->_environment->getLogger()->log(
					"[WRITER] [WORKER] Unable to fork to create the Periodic Saver. Maybe insufficient system ressources or max process limit reached.",
					ILogger::ERR
				);
			}else if ($pid === 0){
				fclose($this->_acquiredLockFile);
				cli_set_process_title(cli_get_process_title()." Periodic Saver");
				file_put_contents(
					$this->_environment->getWorkingDir()."/"
					.$this->_environment->getName()."-PeriodicSaver.pid",
					getmypid()
				);
				$this->_environment->getLogger()->log(
					"$tags Started (pid : ".getmypid().").",
					ILogger::LOG
				);
				while(self::isRunning($this->_environment)){
					$this->_environment->getLogger()->log(
						"$tags Wake up. Sending save query to parent worker...",
						ILogger::LOG
					);
					try{
						$socket = $this->createClientSocket($this->getWorkerSocketAddr());
						$this->write(
							$this->_dataParser->lineariseData(
								new MSServerInternalRequest(
									$this->_workerParams->getServerKey(),
									"",
									TriggerSaveRequest::class,
									$this->_serializer->serialize(new TriggerSaveRequest(
										getmypid()
									))
								)
							),
							$socket
						);
						socket_close($socket);
						$this->_environment->getLogger()->log(
							"$tags Save query successfully sent",
							ILogger::LOG
						);
					}catch(\Exception | \Error $e){
						$this->_environment->getLogger()->log(
							"$tags An error occured while attempting to send the query to the parent worker : $e",
							ILogger::ERR
						);
						$this->errorLog((string)$e);
					}
					//On attend à la fin pour minimiser les problèmes de connexion.
					usleep($this->_saveFrequency);
				}
				$this->_environment->getLogger()->log("$tags Gracefull shutdown : parent worker have been closed.");
				//une fois que le worker est éteint, on quitte.
				exit(0);
			}else{
				$this->_periodicSavePID = $pid;
			}
		}else{
			throw new IllegalInvocation("PeriodicSave can only be called in WORKER_MODE");
		}
	}

	/**
	 * Fork pour effectuer la sauvegarde. Le père enregistre la requête de sauvegarde avec le PID
	 * du fils.
	 * Le fils effectue la sauvegarde et renvoie SaveDoneRequest au père pour qu'il puisse supprimer
	 * la requête de sauvegarde de sa liste.
	 *
	 * @param TriggerSaveRequest $clientRequest Requête de trigger
	 *
	 * @throws IllegalInvocation
	 * @throws MSServerComponentFailure
	 */
	private function forkAndSave(TriggerSaveRequest $clientRequest):void{
		$savePid = pcntl_fork();
		$tags = "[WRITER] [WORKER] [CLIENT SAVING PROCESS]";
		if($savePid === 0 ){
			cli_set_process_title(cli_get_process_title()." saving process");
			$this->_environment->getLogger()->log(
				"$tags Started (pid : ".getmypid().").Changes to models will be persisted...",
				ILogger::LOG
			);
			$models = $this->triggerSave();
			try{
				$socket = $this->createClientSocket($this->getWorkerSocketAddr());
				$this->write(
					$this->_dataParser->lineariseData(
						new MSServerInternalRequest(
							$this->_workerParams->getServerKey(),
							"",
							SaveDoneRequest::class,
							$this->_serializer->serialize(new SaveDoneRequest(
								$clientRequest->getSaveId(),
								$models
							))
						)
					),
					$socket
				);
				socket_close($socket);
			}catch(\Exception $e){
				$this->_environment->getLogger()->log(
					"$tags Cannot send the SaveDone notification on socket ".$this->getWorkerSocketAddr()
					." : $e",
					ILogger::ERR
				);
			}
			exit(0);
		}else if($savePid === -1){
			throw new MSServerComponentFailure("Unable to fork process to apply a save !");
		}else{
			$this->_saveRequests[$clientRequest->getSaveId()] = [
				"date" => microtime(true),
				"pid" => $savePid
			];
		}
	}

	/**
	 * Déclenche la sauvegarde des données en attente.
	 *
	 * @param bool $wait Met en pause le temps que la sauvegarde en cours se termine.
	 *
	 * @return array Liste des models sauvegardés sous la forme "class"=>microtime(true) date de
	 *               dernière modification
	 * @throws IllegalInvocation
	 */
	private function triggerSave(bool $wait = false):array{
		if($this->getWorkerMode() === self::WORKER_MODE){
			$saveLockFile = $this->_environment->getWorkingDir().DS."save.lock";
			touch($saveLockFile);
			$fp = fopen($saveLockFile,"r+");
			if($wait){
				//Bloquant, attend que la sauvegarde précédente se termine.
				$ok = flock($fp,LOCK_EX);
			}else{
				//Ne fait rien si une sauvegarde est déjà en cours.
				$ok = flock($fp,LOCK_EX|LOCK_NB);
			}
			$this->_environment->getLogger()->log(
				"[WRITER] [WORKER] Models successfully persisted.",
				ILogger::LOG
			);
			if($ok){
				$res = $this->_workerParams->getModelManager()->save();
				flock($fp,LOCK_UN);
				fclose($fp);
				unlink($saveLockFile);
				return $res;
			}else{
				return [];
			}
		}else{
			throw new IllegalInvocation("Cannot use triggerSave in WORKER_MODE");
		}
	}

	/**
	 * @return string Chemin d'accés à la socket du worker
	 */
	private function getWorkerSocketAddr():string{
		return $this->_workerSocket;
	}

	/**
	 *  WORKER_MODE : éteint proprement le worker
	 *  CLIENT_MODE : envoie la commande d'extinction au worker
	 *
	 * @param null|string $serverKey Clé serveur
	 *
	 * @throws IllegalInvocation
	 * @throws InactiveKVSContainerWorkerException
	 */
	public function shutdown(?string $serverKey=null):void{
		if($this->getWorkerMode() === self::WORKER_MODE){
			if(is_resource($this->_socket)){
				socket_close($this->_socket);
				$this->_socket = null;
			}
			posix_kill($this->_periodicSavePID,PCNTLSignalsHelper::SIGINT);
			if(file_exists($this->getWorkerSocketAddr())){
				unlink($this->getWorkerSocketAddr());
			}
			if(file_exists($this->_pidFilePath)){
				unlink($this->_pidFilePath);
			}

			flock($this->_acquiredLockFile,LOCK_UN);
			fclose($this->_acquiredLockFile);
			unlink($this->_lockFile);
			$this->_environment->getLogger()->log(
				"[WRITER] [WORKER] Gracefull shutdown.",
				ILogger::LOG
			);

			exit(0);
		}else{
			$this->sendQuery(
				new MSServerInternalRequest(
					$serverKey,
					"",
					ShutdownWriterRequest::class,
					$this->_serializer->serialize(new ShutdownWriterRequest())
				)
			);
		}
	}

	/**
	 *  Envoie une requête au worker
	 *
	 * @param IMSServerInternalRequest $request Requête à envoyer
	 *
	 * @throws IllegalInvocation
	 * @throws InactiveKVSContainerWorkerException
	 */
	public function sendQuery(IMSServerInternalRequest $request):void{
		$tries = 0;
		$max_tries = $this->_maxAttempts;
		$sleep = $this->_delay;
		while($tries<$max_tries && !$this->attemptToSendRequestToWorker($request)){
			$tries ++;
			$this->start(false);
			usleep($sleep);
		}
		if($tries>=$max_tries){
			$this->start(false);
			//Si on a dépassé le nombre d'essais, et que le worker n'est toujours pas joignable,
			//on envoie l'erreur au client et on passe à la requête suivante.
			$error = new InternalRequestTimeout(
				$this->_lastAttemptError
				?? new MSServerComponentFailure("Unable to contact writer worker.")
			);
			$this->_environment->getLogger()->log(
				"[WRITER] [CLIENT] Unable to connect to worker ($tries attempts). Last error : $this->_lastAttemptError",
				ILogger::ERR
			);
			$this->_lastAttemptError = null;
			//On tente de renvoyer la réponse sur la socket du MSServeur.
			try{
				$socket = $this->createClientSocket($this->getResponseSocketName());
				$this->write(
					$this->_dataParser->lineariseData(
						new WriterResponse(
							$request->getQueryId(),
							$error
						)
					),
					$socket
				);
				socket_close($socket);
			}catch(\Exception $e){
				$this->_environment->getLogger()->log(
					"[WRITER] [CLIENT] Unable to send error report to MSServer : ".print_r([
					$e->getMessage(),
					$e->getFile(),
					$e->getLine(),
					$e->getTraceAsString()
				],true),ILogger::ERR);
			}
		}
	}

	/**
	 * @brief Tente d'envoyer une requête au worker.
	 *
	 * @param IMSServerInternalRequest $request Requête à envoyer
	 *
	 * @return bool True si la requête est envoyée, false sinon
	 * @throws IllegalInvocation
	 */
	private function attemptToSendRequestToWorker(IMSServerInternalRequest $request):bool{
		if($this->getWorkerMode() === self::CLIENT_MODE){
			try{
				$socket = $this->createClientSocket($this->getWorkerSocketAddr());
				$this->write(
					$this->_dataParser->lineariseData($request),
					$socket
				);
				socket_close($socket);
				return true;
			}catch(\Exception | \Error $e){
				$this->_lastAttemptError = $e;
				return false;
			}
		}else{
			throw new IllegalInvocation("Cannot use sendQuery in WORKER_MODE");
		}
	}

	/**
	 *  Envoie une réponse vers le MSServer
	 *
	 * @param IMSServerComponentResponse $response Réponse à transmettre
	 *
	 * @throws IllegalInvocation
	 */
	public function sendResponse(IMSServerComponentResponse $response):void{
		if($this->getWorkerMode() === self::WORKER_MODE){
			$socket = $this->createClientSocket($this->getResponseSocketName());
			try{
				$this->write(
					$this->_dataParser->lineariseData($response),
					$socket
				);
				$this->_environment->getLogger()->log(
					"[WRITER] [WORKER] Response successfully sent to the MSServer (query id : "
					.$response->getQueryId().")",
					ILogger::LOG
				);
			}catch(\Exception $e){
				$socketError = socket_last_error();
				$this->_environment->getLogger()->log(
					"[WRITER] [WORKER] Unable to send response to the MSServer : ".print_r([
						"query_id"=>$response->getQueryId(),
						"socket" => [
							"code" => $socketError,
							"str" => socket_strerror($socketError)
						],
						"Exception" => $e
					],true),
					ILogger::ERR
				);
			}
		}else{
			throw new IllegalInvocation("Cannot use sendResponse in CLIENT_MODE");
		}
	}

	/**
	 * @return string
	 */
	public function getResponseSocketName():string{
		return $this->_socketAddr;
	}

	/**
	 *  Crée une socket et la paramètre avec le timeout défini par $this->_socketTiemout
	 * @param string $addr
	 *
	 * @return resource
	 */
	private function createClientSocket(string $addr){
		$socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		$this->configureSocket($socket);
		socket_connect($socket,$addr);
		return $socket;
	}
}