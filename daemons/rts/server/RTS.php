<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 17:01
 */

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\app\events\ClientConnected;
use wfw\daemons\rts\server\app\events\ClientDisconnected;
use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppResponseEvent;
use wfw\daemons\rts\server\app\events\RTSAppEventObserver;
use wfw\daemons\rts\server\app\events\RTSCloseConnectionsEvent;
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
	/** @var RTSNetworkPort $_worker */
	private $_worker;
	/** @var int $_sleepInterval */
	private $_sleepInterval;
	/** @var string $_instanceName */
	private $_instanceName;
	/** @var string $_logHead */
	private $_logHead;
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
		cli_set_process_title("WFW RTS $this->_instanceName");
		$this->_logHead = "[RTS] [$this->_instanceName]";
	}

	public function start():void{
		$sockets =  stream_socket_pair(STREAM_PF_UNIX,STREAM_SOCK_STREAM, 0);
		$this->_localPortPid = pcntl_fork();
		if($this->_localPortPid === 0){
			cli_set_process_title(cli_get_process_title()." Local Port");
			$localPort = new RTSLocalPort(
				$this->_socketPath,
				$this->_environment->getWorkingDir(),
				$sockets[0],
				$this->_protocol,
				$this->_environment,
				$this->_serializer,
				$this->_secretKey,
				$this->_requestTtl,
				$this->_sendErrorToClient,
				$this->_logHead
			);
			$localPort->start();
			exit(1); //If something goes wrong
		}else if($this->_localPortPid > 0){
			$this->_environment->getLogger()->log("$this->_logHead Started (".getmypid().")");
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
		$sockets = stream_socket_pair(STREAM_PF_UNIX,STREAM_SOCK_STREAM, 0);
		$this->_mainProcessSocket = $sockets[0];
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
			$this->_sleepInterval,
			"$this->_logHead [NetworkPort]"
		);
		if(is_null($this->_networkPort)) $this->_networkPort = $this->_worker->getNetworkSocket();
		$pid = pcntl_fork();
		if($pid === 0){
			cli_set_process_title(cli_get_process_title()." Network Port");
			$this->configureSocket($sockets[0]);
			//cleanup not needed stuff
			$this->_workers = [];
			$this->_workersInfos = [];
			$this->_workersBySocketId = [];
			$this->_clientsByWorkerPid = [];
			$this->_worker->start();
			return -1;
		}else if($pid > 0){
			$pid = (string) $pid;
			$this->_workers[$pid] = $sockets[1];
			$this->_workersBySocketId[(string)(int)$sockets[1]] = $pid;
			$this->configureSocket($sockets[1]);
			$this->_workersInfos[$pid] = [];
			return $pid;
		}else throw new \Exception("Unable to fork !");
	}

	/**
	 * @throws MaxWorkerLimitReached
	 */
	private function workerManagerLoop():void{
		if($this->_spawnAllWorkersAtStartup) for($i=0; $i < $this->_maxWorkers; $i++){
			$this->newWorker();
		} else $this->newWorker();
		while(true){
			try{
				$this->workerManager();
			}catch(\Error | \Exception $e){
				$this->_environment->getLogger()->log("$e",ILogger::ERR);
			}
		}
	}

	private function workerManager():void{
		$start = microtime(true);
		$master = [$this->_networkPort];
		$local = [$this->_localPort];
		$sockets = array_merge(
			[$this->_localPort,$this->_networkPort],
			array_values($this->_workers)
		);
		$chunks = $this->splitIntoChunks($sockets);
		//bypass the 1024 socket_select limit
		foreach($chunks as $chunk){
			$read = $chunk; $write = []; $except = [];
			stream_select($read,$write,$except,(count($chunks) === 1 && count($chunk)<1024) ? null : 0);
			foreach($read as $socket){
				if($socket === $this->_networkPort){
					$this->accept();
				}else if($socket === $this->_localPort){
					try{
						$this->dataTransmission(null,true,$this->_serializer->unserialize(
							$this->read($this->_localPort)
						));
						$read = array_diff($read,$local);
					}catch(\Error | \Exception $e){
						$this->_environment->getLogger()->log(
							"An error occured while trying to read on local port : $e",
							ILogger::ERR
						);
					}
				}else{
					foreach($this->_workers as $pid => $w){
						if($w === $socket){
							try{
								$this->processWorkerSocket($w,$pid);
								break;
							}catch(SocketFailure $e){
								//If a worker dropped the connection, or died for somewhat reason,
								//clean it up.
								posix_kill($pid,PCNTLSignalsHelper::SIGALRM);
								stream_socket_shutdown($this->_workers[$pid],STREAM_SHUT_RDWR);
								unset($this->_workers[$pid]);
								unset($this->_workersInfos[$pid]);
							}
						}
					}
				}
			}
		}
		$execTime = microtime(true) - $start;
		if($execTime < $this->_sleepInterval) usleep($this->_sleepInterval - $execTime);
	}

	/**
	 * @param        $s
	 * @param string $pid
	 */
	private function processWorkerSocket($s,string $pid){
		while(strlen($wData = $this->read($s))>0){
			try{
				$decoded = $this->_serializer->unserialize($wData);
				if($decoded instanceof InternalCommand){
					if($decoded->getRootKey() === $this->_secretKey){
						if($decoded->getName() === InternalCommand::DATA_TRANSMISSION){
							$this->dataTransmission(
								$pid,
								$decoded->getSource() === InternalCommand::LOCAL,
								...$decoded->getData()
							);
						}else $this->_environment->getLogger()->log(
							"$this->_logHead Unsupported command "
							.$decoded->getName()." given (ignored).",
							ILogger::ERR
						);
					}else $this->_environment->getLogger()->log(
						"$this->_logHead Wrong secret key given by "
						.$decoded->getSource()." ($pid) for command ".$decoded->getName()
						.". Request ignored.",
						ILogger::ERR
					);
				}else $this->_environment->getLogger()->log(
					"$this->_logHead Expected ".InternalCommand::class." but "
					.gettype($decoded)." given.",
					ILogger::ERR
				);
			}catch(\Error | \Exception $e){
				$this->_environment->getLogger()->log(
					"$this->_logHead Unable to decode worker $pid data : $e",
					ILogger::ERR
				);
			}
		}
	}

	/**
	 * @param string         $pid
	 * @param bool           $local
	 * @param IRTSAppEvent[] $events
	 */
	private function dataTransmission(?string $pid,bool $local =false, IRTSAppEvent ...$events):void{
		$selfApply = [];
		$distribued = [];
		$workersToSend = [];
		/** @var IRTSAppEvent $e */
		foreach($events as $e){
			if($e instanceof RTSCloseConnectionsEvent && $local) continue;
			if($e instanceof ClientConnected){
				if(!$local) $this->clientConnected($pid,$e);
				else continue;
			}
			else if($e instanceof ClientDisconnected && !$local){
				if(!$local) $this->clientDisconnected($pid,$e);
				else continue;
			}
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
		foreach($workersToSend as $wpid=>$events) if($wpid !== $pid){
			$this->write($this->_workers[$wpid],$this->_serializer->serialize(new InternalCommand(
				InternalCommand::ROOT,
				InternalCommand::DATA_TRANSMISSION,
				$this->_serializer->serialize($events),
				null,
				$this->_secretKey
			)));
		}
	}

	/**
	 * Called when a worker send a feedback.
	 *
	 * @param string          $pid worker's pid
	 * @param ClientConnected $event
	 */
	private function clientConnected(string $pid, ClientConnected $event):void{
		$cid = $event->getConnection()->getId();
		$this->_clientsByWorkerPid[$cid] = $pid;
		$this->_workersInfos[$pid][$cid] = $event->getConnection();
		if(!isset($this->_clientsByApp[$event->getConnection()->getApp()]))
			$this->_clientsByApp[$event->getConnection()->getApp()] = [];
		$this->_clientsByApp[$event->getConnection()->getApp()][$cid] = true;
		$this->_environment->getLogger()->log("$this->_logHead New client connected : $cid");
		$this->dataTransmission($pid,$event);
	}

	/**
	 * Cleanup connections while receive a closed feedback from worker
	 *
	 * @param string             $pid
	 * @param ClientDisconnected $event
	 */
	private function clientDisconnected(string $pid, ClientDisconnected $event):void{
		$cid = $event->getConnection()->getId();
		if(isset($this->_clientsByWorkerPid[$cid]))
			unset($this->_clientsByWorkerPid[$cid]);
		if(isset($this->_workersInfos[$pid][$cid]))
			unset($this->_workersInfos[$pid][$cid]);
		if(isset($this->_clientsByApp[$event->getConnection()->getApp()][$cid]))
			unset($this->_clientsByApp[$event->getConnection()->getApp()][$cid]);
		$this->_environment->getLogger()->log("$this->_logHead Client disconnected : $cid");
		$this->dataTransmission($pid,$event);
	}

	/**
	 * Cherche un worker pouvant accepter une nouvelle connection.S'il ne trouve pas, ordonne à un worker
	 * de refuser le nouveau client.
	 */
	private function accept():void{
		$this->_environment->getLogger()->log(
			"$this->_logHead New incoming connection found. Trying to find a worker to handle it..."
		);
		$accepterFound = false;
		foreach($this->_workersInfos as $pid=>$infos){
			if(count($infos) < $this->_maxWSockets){
				$this->_environment->getLogger()->log("$this->_logHead Worker $pid selected.");
				$this->write($this->_workers[$pid],$this->_serializer->serialize(new InternalCommand(
					InternalCommand::ROOT,
					InternalCommand::CMD_ACCEPT,
					null, null, $this->_secretKey
				)));
				$accepterFound = true;
				break;
			}
		}
		if(!$accepterFound){
			try{ $this->newWorker(); } catch (MaxWorkerLimitReached $e){
				$less=null; $pid=null;
				foreach($this->_workersInfos as $k=>$v){
					if(is_null($less)){
						$less = count($v);
						$pid = $k;
					} else if($less > count($v)){
						$less = count($v);
						$pid = $k;
					}
				}
				//S'il n'y a pas de limite de clients par worker, ou si le worker en question peut
				//encore recevoir de nouveau client, on lui demande d'accepter la connexion
				if($this->_allowedOverflow<0 || $less <= $this->_allowedOverflow*$this->_maxWSockets){
					$this->_environment->getLogger()->log("$this->_logHead Worker $pid selected.");
					$this->write($this->_workers[$pid],$this->_serializer->serialize(new InternalCommand(
						InternalCommand::ROOT, InternalCommand::CMD_ACCEPT,
						null, null, $this->_secretKey
					)));
				}else{
					$this->_environment->getLogger()->log("$this->_logHead Worker $pid selected to reject the connexion.");
					$this->write($this->_workers[$pid],$this->_serializer->serialize(new InternalCommand(
						InternalCommand::ROOT,
						InternalCommand::CMD_REJECT,
						"Max server connections reached.",
						null,
						$this->_secretKey
					)));
					$this->_environment->getLogger()->log(
						"$this->_logHead Client rejected : max server connections reached.",
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
	 * @param resource $socket Configure la socket
	 */
	private function configureSocket($socket){
		stream_set_blocking($socket,false);
	}

	/**
	 * Eteint le serveur RTS et tous ses workers.
	 *
	 * @param \Exception|null $e
	 */
	public function shutdown(?\Exception $e=null):void{
		$this->_environment->getLogger()->log("$this->_logHead Shutting down current instance...");
		flock($this->_acquiredLockFile,LOCK_UN);
		fclose($this->_acquiredLockFile);
		if(file_exists($this->_lockFile)) unlink($this->_lockFile);
		$cmd = $this->_serializer->serialize(new InternalCommand(
			InternalCommand::ROOT,
			InternalCommand::SHUTDOWN,
			null,
			null,
			$this->_secretKey
		));
		$this->_environment->getLogger()->log("$this->_logHead Sending close command to LocalPort...");
		if(!is_null($this->_localPort)){
			try{
				$this->write($this->_localPort,$cmd);
				stream_socket_shutdown($this->_localPort,STREAM_SHUT_RDWR);
			}catch(\Error | \Exception $e){}
		}
		$this->_environment->getLogger()->log("$this->_logHead Close command sent.");

		$this->_environment->getLogger()->log("$this->_logHead Sending close commands to all NetworkPort workers...");
		foreach($this->_workersInfos as $pid=>$info){
			$this->write($this->_workers[$pid],$cmd);
		}
		$this->_environment->getLogger()->log("$this->_logHead Close commands sent.");

		$this->_environment->getLogger()->log("$this->_logHead Gracefull shutdown.");
		if(!is_null($e)){
			$this->_environment->getLogger()->log($e,ILogger::ERR);
			exit(1);
		}else exit(0);
	}
}