<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 17:01
 */

namespace wfw\daemons\rts\server;

use PHPMailer\PHPMailer\Exception;
use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppResponseEvent;
use wfw\daemons\rts\server\app\events\RTSAppEventObserver;
use wfw\daemons\rts\server\app\RTSAppsManager;
use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\errors\MaxWorkerLimitReached;
use wfw\daemons\rts\server\worker\InternalCommand;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\errors\SocketFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;

/**
 * RealTimeServer
 */
final class RTS{
	public const MAX_SOCKET_SELECT = 1000;
	/** @var resource $_localPort */
	private $_localPort;
	/** @var string $_secretKey */
	private $_secretKey;
	/** @var string $_socketPath */
	private $_socketPath;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var IRTSEnvironment $_environment */
	private $_environment;
	/** @var int $_requestTtl */
	private $_requestTtl;
	/** @var bool $_sendErrorToClient */
	private $_sendErrorToClient;
	/** @var int $_localPortPid */
	private $_localPortPid;
	/** @var int $_port */
	private $_port;
	/** @var string $_host */
	private $_host;
	/** @var $_networkPort */
	private $_networkPort;
	/** @var bool|resource $_acquiredLockFile */
	private $_acquiredLockFile;
	/** @var string $_lockFile */
	private $_lockFile;
	/** @var int $_maxWSockets */
	private $_maxWSockets;
	/** @var int $_maxWorkers */
	private $_maxWorkers;
	/** @var int $_allowedOverflow */
	private $_allowedOverflow;
	/** @var resource[] $_workers */
	private $_workers;
	/** @var array $_clientsByApp */
	private $_clientsByApp;
	/** @var string[] $_workersBySocketId */
	private $_workersBySocketId;
	/** @var string[] $_clientsByWorkerPid */
	private $_clientsByWorkerPid;
	/** @var resource $_mainProcessSocket */
	private $_mainProcessSocket;
	/** @var array $_workersInfos */
	private $_workersInfos;
	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);
	/** @var RTSNetworkPort $_worker */
	private $_worker;
	/** @var int $_sleepInterval */
	private $_sleepInterval;
	/** @var string $_instanceName */
	private $_instanceName;
	/** @var string $_procName */
	private $_procName;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var RTSAppsManager $_appsManager */
	private $_appsManager;
	/** @var bool $_spawnAllWorkersAtStartup */
	private $_spawnAllWorkersAtStartup;

	/**
	 * RTS constructor.
	 *
	 * @param string          $instanceName
	 * @param string          $socketPath              Chemin vers la socket locale du serveur
	 * @param string          $host                    Websocket host
	 * @param int             $port                    Port network
	 * @param ISocketProtocol $protocol                Protocol de communication sur la socket serveur
	 * @param IRTSEnvironment $environment             Environement du serveur
	 * @param ISerializer     $serializer
	 * @param int             $maxWSocket              Nombre maximum de requêtes par worker (0 pour no-limit)
	 * @param int             $maxWorkers              Nombre maximum de workers (0 pour no-limit)
	 * @param int             $allowedWSocketsOverflow Nombre de fois que l'on peut augmenter le nombre
	 *                                                 maximum de sockets par worker lorsque le nombre maximum de
	 *                                                 worker est atteint
	 *                                                 -1 : pas de limite, n : max_wsockets * (n+1)
	 * @param bool            $spawnAllWorkersAtStartup
	 * @param int             $requestTtl              Durée maximum de chaque requête
	 * @param int             $sleepInterval           Sleep interval between two loops (in ms)
	 * @param bool            $sendErrorToClient       Envoir les erreurs sur les clients socket locale
	 * @throws IllegalInvocation
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $instanceName,
		string $socketPath,
		string $host,
		int $port,
		ISocketProtocol $protocol,
		IRTSEnvironment $environment,
		ISerializer $serializer,
		int $maxWSocket = 1,
		int $maxWorkers = 8,
		int $allowedWSocketsOverflow = -1,
		bool $spawnAllWorkersAtStartup = true,
		int $requestTtl = 900,
		int $sleepInterval = 100,
		bool $sendErrorToClient = true
	){
		$this->_port = $port;
		$this->_host = $host;
		$this->_workers = [];
		$this->_clientsByApp = [];
		$this->_workersInfos = [];
		$this->_protocol = $protocol;
		$this->_workersBySocketId = [];
		$this->_clientsByWorkerPid = [];
		$this->_maxWorkers = $maxWorkers;
		$this->_socketPath = $socketPath;
		$this->_requestTtl = $requestTtl;
		$this->_serializer = $serializer;
		$this->_maxWSockets = $maxWSocket;
		$this->_environment = $environment;
		$this->_instanceName = $instanceName;
		$this->_sleepInterval = $sleepInterval;
		$this->_sendErrorToClient = $sendErrorToClient;
		$this->_allowedOverflow = $allowedWSocketsOverflow;
		$this->_spawnAllWorkersAtStartup = $spawnAllWorkersAtStartup;
		$this->_secretKey = (string) new UUID(UUID::V4);
		$this->_appsManager = new RTSAppsManager(
			new RTSAppEventObserver(),
			$this->_environment->getModules()
		);

		//On commence par vérifier l'existence du fichier lock
		//Un seul RTS est autorisé par repertoir de travail.
		$this->_lockFile = $environment->getWorkingDir()."/server.lock";
		if(!file_exists($this->_lockFile)){
			touch($this->_lockFile);
		}

		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($this->_lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		if($res) throw new IllegalInvocation(
			"A RTS instance is already running for this directory !"
		);
		cli_set_process_title($this->_mainProcessSocket = "[RTS] [$this->_instanceName]");
	}

	public function start():void{
		$sockets =  [];
		socket_create_pair(AF_UNIX,SOCK_STREAM,SOL_TCP,$sockets);
		$this->_localPortPid = pcntl_fork();
		if($this->_localPortPid === 0){
			cli_set_process_title("$this->_procName [LocalPort] [".getmypid()."]");
			socket_close($sockets[1]);
			$localPort = new RTSLocalPort(
				$this->_socketPath,
				$this->_environment->getWorkingDir(),
				$sockets[0],
				$this->_protocol,
				$this->_environment,
				$this->_requestTtl,
				$this->_sendErrorToClient
			);
			$localPort->start();
			exit(1); //If something goes wrong
		}else if($this->_localPortPid > 0){
			socket_close($sockets[0]);
			$this->_localPort = $sockets[1];
			$this->workerManagerLoop();
		}else throw new \Exception("Unable to fork !");
	}

	/**
	 * Crée un nouveau worker
	 *
	 * @return int pid du worker créé
	 * @throws MaxWorkerLimitReached
	 * @throws \RuntimeException
	 */
	private function newWorker():int{
		if(count($this->_workers) > $this->_maxWorkers) throw new MaxWorkerLimitReached(
			count($this->_workers)." workers already created !"
		);
		$sockets = [];
		socket_create_pair(AF_UNIX,SOCK_STREAM,SOL_TCP,$sockets);
		$pid = pcntl_fork();
		if($pid === 0){
			socket_close($sockets[1]);
			$this->_mainProcessSocket = $sockets[0];
			$this->configureSocket($sockets[0]);
			//cleanup not needed stuff
			foreach($this->_workers as $pid=>$w){
				socket_close($w);
				socket_close($this->_localPort);
			}
			$this->_workers = [];
			$this->_workersInfos = [];
			$this->_environment = null;
			$this->_workersBySocketId = [];
			$this->_clientsByWorkerPid = [];
			$this->wsLoop();
			return -1;
		}else if($pid > 0){
			$pid = (string) $pid;
			socket_close($sockets[0]);
			$this->_workers[$pid] = $sockets[1];
			$this->_workersBySocketId[(string)(int)$sockets[1]] = $pid;
			$this->configureSocket($sockets[1]);
			$this->_workersInfos[$pid] = [
				"clients" => []
			];
			return $pid;
		}else throw new \Exception("Unable to fork !");
	}

	/**
	 * @throws MaxWorkerLimitReached
	 */
	private function workerManagerLoop():void{
		if($this->_spawnAllWorkersAtStartup) for($i=0; $i < $this->_maxWorkers; $i++)
			$this->newWorker();
		else $this->newWorker();
		while(true){
			$start = microtime(true);
			$master = [$this->_networkPort];
			$local = [$this->_localPort];
			$chunks = $this->splitIntoChunks(array_merge(
				[$this->_localPort,$this->_networkPort],
				array_values($this->_workers))
			);
			//bypass the 1024 socket_select limit
			foreach($chunks as $chunk){
				$read = $chunk; $write = null; $except = null;
				socket_select($read,$write,$except,0);
				if(count(array_intersect($master,$chunk)) === 1){
					$this->accept();
					$read = array_diff($read,$master);
				}
				if(count(array_intersect($read,$local)) === 1){
					try{
						$this->dataTransmission(null,$this->_serializer->unserialize(
							$this->read($this->_localPort)
						));
						$read = array_diff($read,$local);
					}catch(\Error | \Exception $e){
						$this->_environment->getLogger()->log(
							"An error occured whil trying to read on local port : $e",
							ILogger::ERR
						);
					}
				}
				foreach(array_diff($read,[$local,$master]) as $s){
					foreach(array_diff($this->_workers,[$s]) as $pid=>$w){
						try{
							while(strlen($wData = $this->read($s))>0){
								if($wData === "connection_closed") $this->_workersInfos[$pid]["clients"]--;
								else{
									$decoded = json_decode($wData,true);
									$error = false;
									if(json_last_error() === JSON_ERROR_NONE){
										$key = $decoded["root_key"] ?? '';
										$cmd = $decoded["cmd"] ?? '';
										$source = $decoded["source"] ?? '';
										$data = $decoded["data"] ?? null;
										if($key !== $this->_secretKey){
											$this->_environment->getLogger()->log(
												"$this->_procName Wrong secret key given by $source ($pid) for command $cmd. Request ignored.",
												ILogger::ERR
											);
											continue;
										}
										switch($cmd){
											case InternalCommand::FEEDBACK_CLIENT_CREATED:
												$this->clientConnected(
													$pid,
													json_decode($data,true)
												);
												break;
											case InternalCommand::FEEDBACK_CLIENT_DISCONNECTED:
												$this->clientDisconnected(
													$pid,
													json_decode($data,true)
												);
												break;
											case InternalCommand::DATA_TRANSMISSION :
												$this->dataTransmission(
													$pid,
													$this->_serializer->unserialize($data)
												);
												break;
											default :
												$error = true;
												break;
										}
									}else $error = true;
									if($error) $this->_environment->getLogger()->log(
										"$this->_procName Invalid query recieved from worker "
										.$this->_workersBySocketId[(int)$s]." : $wData",
										ILogger::WARN
									);
								}
							}
						}catch(SocketFailure $e){
							//If a worker dropped the connection, or died for somewhat reason,
							//clean it up.
							posix_kill($pid,PCNTLSignalsHelper::SIGALRM);
							socket_close($this->_workers[$pid]);
							unset($this->_workers[$pid]);
							unset($this->_workersInfos[$pid]);
						}
					}
				}
			}
			$execTime = microtime(true) - $start;
			if( $execTime < $this->_sleepInterval) usleep($execTime - $start);
		}
	}

	/**
	 * @param string $pid
	 * @param IRTSAppEvent[]  $events
	 */
	private function dataTransmission(?string $pid, array $events):void{
		$selfApply = [];
		$distribued = [];
		$workersToSend = [];
		/** @var IRTSAppEvent $e */
		foreach($events as $e){
			if($e->distributionMode(IRTSAppEvent::CENTRALIZATION)) $selfApply[] = $e;
			if($e->distributionMode(IRTSAppEvent::DISTRIBUTION)){
				if($e instanceof IRTSAppResponseEvent){
					$recipients = $e->getRecipients();
					$exepts  = $e->getExcepts();
					if(is_null($recipients)) $sList = array_keys($this->_clientsByWorkerPid);
					else $sList = array_diff($recipients,$exepts);
					foreach($sList as $s){
						if(isset($this->_clientsByWorkerPid[$s])){
							if(!isset($workersToSend[$this->_clientsByWorkerPid[$s]]))
								$workersToSend[$this->_clientsByWorkerPid[$s]] = [];
							$workersToSend[$this->_clientsByWorkerPid[$s]][] = $s;
						}
					}
				}else $distribued[] = $e;
			}
		}
		if(!empty($selfApply)) $this->_appsManager->dispatch(...$selfApply);
		foreach($workersToSend as $wpid=>$events) if($wpid !== $pid)
			$this->write($this->_workers[$wpid],new InternalCommand(
				InternalCommand::ROOT,
				InternalCommand::DATA_TRANSMISSION,
				$this->_serializer->serialize($events),
				null,
				$this->_secretKey
			));
	}

	/**
	 * Called when a worker send a feedback.
	 * @param string   $pid Pid du worker
	 * @param null|array $connectionInfos Set connections infos in worker
	 */
	private function clientConnected(string $pid, $connectionInfos):void{
		if(is_null($connectionInfos) || !is_array($connectionInfos)){
			$this->_environment->getLogger()->log(
				"$this->_procName Unable to connect client : invalid connectionInfos "
				."(request sent by Network Port worker $pid).",
				ILogger::ERR
			);
			return;
		}
		$this->_clientsByWorkerPid[$connectionInfos["id"]] = $pid;
		$this->_workersInfos[$pid][$connectionInfos["id"]] = $connectionInfos;
		if(!isset($this->_clientsByApp[$connectionInfos["app"]]))
			$this->_clientsByApp[$connectionInfos["app"]] = [];
		$this->_clientsByApp[$connectionInfos["app"]][$connectionInfos["id"]] = true;
		$this->_environment->getLogger()->log(
			"$this->_procName New client connected : ".json_encode($connectionInfos)
		);
	}

	/**
	 * Cleanup connections while receive a closed feedback from worker
	 * @param string     $pid
	 * @param array|null $connectionInfos
	 */
	private function clientDisconnected(string $pid, $connectionInfos):void{
		if(is_null($connectionInfos) || !is_array($connectionInfos)){
			$this->_environment->getLogger()->log(
				"$this->_procName Unable to disconnect client : invalid connectionInfos "
				."(request sent by Network Port worker $pid).",
				ILogger::ERR
			);
			return;
		}
		if(isset($this->_clientsByWorkerPid[$connectionInfos["id"]]))
			unset($this->_clientsByWorkerPid[$connectionInfos["id"]]);
		if(isset($this->_workersInfos[$pid][$connectionInfos["id"]]))
			unset($this->_workersInfos[$pid][$connectionInfos["id"]]);
		if(isset($this->_clientsByApp[$connectionInfos["app"]][$connectionInfos["id"]]))
			unset($this->_clientsByApp[$connectionInfos["app"]][$connectionInfos["id"]]);
		$this->_environment->getLogger()->log(
			"$this->_procName Client disconnected : ".json_encode($connectionInfos)
		);
	}

	/**
	 * Cherche un worker pouvant accepter une nouvelle connection.S'il ne trouve pas, ordonne à un worker
	 * de refuser le nouveau client.
	 */
	private function accept():void{
		$accepterFound = false;
		foreach($this->_workersInfos as $pid=>$infos){
			if($infos["clients"]<$this->_maxWSockets){
				$this->write($this->_workers[$pid],new InternalCommand(
					InternalCommand::ROOT,
					InternalCommand::CMD_ACCEPT,
					null, null, $this->_secretKey
				));
				$this->_workersInfos[$pid]["clients"]++;
				$accepterFound = true;
				break;
			}
		}
		if(!$accepterFound){
			try{ $this->newWorker(); } catch (MaxWorkerLimitReached $e){
				$less=null; $pid=null;
				foreach($this->_workersInfos as $k=>$v){
					if(is_null($less)){
						$less = $v["clients"];
						$pid = $k;
					} else if($less > $v["clients"]){
						$less = $v["clients"];
						$pid = $k;
					}
				}
				//S'il n'y a pas de limite de clients par worker, ou si le worker en question peut
				//encore recevoir de nouveau client, on lui demande d'accepter la connexion
				if($this->_allowedOverflow<0 || $less <= $this->_allowedOverflow*$this->_maxWSockets)
					$this->write($this->_workers[$pid],new InternalCommand(
						InternalCommand::ROOT, InternalCommand::CMD_ACCEPT,
						null, null, $this->_secretKey
					));
				else{
					$this->write($this->_workers[$pid],new InternalCommand(
						InternalCommand::ROOT,
						InternalCommand::CMD_REJECT,
						"Max server connections reached.",
						null,
						$this->_secretKey
					));
					$this->_environment->getLogger()->log(
						"$this->_procName Client rejected : max server connections reached.",
						ILogger::WARN
					);
				}
			}
		}
	}

	/**
	 * La fonction socket_select est limitée à 1024 sockets. Donc on fait en sorte de spliter le
	 * tableau en portions de self::MAX_SOCKET_SELECT
	 * @param resource[] &$sockets Liste des sockets dans un tableau linéaire
	 * @return resource[] Tableau de chuncks du tableau passé en paramètres.
	 */
	private function splitIntoChunks(array &$sockets):array{
		$res = [];
		$current = [];
		$i = 1;
		foreach($sockets as $v){
			$current[]=$v;
			if($i%self::MAX_SOCKET_SELECT === 0){
				$res[] = $current;
				$current = [];
			}
			$i++;
		}
		if(!empty($current)) $res[] = $current;
		return $res;
	}

	/**
	 * Lis les données d'une socket
	 * @param resource $socket
	 * @return string
	 */
	private function read($socket):string{
		return $this->_protocol->read($socket);
	}

	/**
	 * Ecrit des données dans une socket
	 * @param resource $socket
	 * @param string $data
	 */
	private function write($socket,string $data):void{
		$this->_protocol->write($socket,$data);
	}

	/**
	 * Loop du port network
	 *
	 * @throws \RuntimeException
	 */
	private function wsLoop():void{
		cli_set_process_title("$this->_procName [NetworkPort] [".getmypid()."]");
		$this->_worker = new RTSNetworkPort(
			$this->_host,
			$this->_port,
			$this->_mainProcessSocket,
			$this->_environment,
			$this->_protocol,
			$this->_appsManager,
			$this->_serializer,
			$this->_secretKey,
			$this->_networkPort,
			$this->_sleepInterval
		);
		if(is_null($this->_networkPort)) $this->_networkPort = $this->_worker->getNetworkSocket();
		$this->_worker->start();
	}

	/**
	 * @param resource $socket Configure la socket
	 */
	private function configureSocket($socket){
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
	}

	/**
	 * Eteint le serveur RTS et tous ses workers.
	 * @param null|Exception $e
	 */
	public function shutdown(?Exception $e=null):void{
		flock($this->_acquiredLockFile,LOCK_UN);
		fclose($this->_acquiredLockFile);
		unlink($this->_lockFile);
		if(!is_null($this->_localPort)) socket_close($this->_localPort);
		if(!is_null($this->_networkPort)) socket_close($this->_networkPort);

		foreach($this->_workersInfos as $pid=>$info){
			posix_kill($pid,PCNTLSignalsHelper::SIGALRM);
		}

		if(!is_null($e)){
			$this->_environment->getLogger()->log($e,ILogger::ERR);
			exit(1);
		}else exit(0);
	}
}