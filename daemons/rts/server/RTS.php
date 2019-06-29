<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 17:01
 */

namespace wfw\daemons\rts\server;

use PHPMailer\PHPMailer\Exception;
use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\errors\MaxWorkerLimitReached;
use wfw\daemons\rts\server\websocket\WebsocketEventObserver;
use wfw\daemons\rts\server\websocket\WebsocketProtocol;
use wfw\daemons\rts\server\worker\WorkerCommand;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\errors\SocketFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\types\UUID;

/**
 * RealTimeServer
 */
final class RTS{
	private const MAX_SOCKET_SELECT = 1000;
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
	/** @var resource $_mainProcessSocket */
	private $_mainProcessSocket;
	/** @var array $_workersInfos */
	private $_workersInfos;
	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);

	/**
	 * RTS constructor.
	 *
	 * @param string          $socketPath        Chemin vers la socket locale du serveur
	 * @param int             $port              Port network
	 * @param ISocketProtocol $protocol          Protocol de communication sur la socket serveur
	 * @param IRTSEnvironment $environment       Environement du serveur
	 * @param int             $maxWSocket        Nombre maximum de requêtes par worker (0 pour no-limit)
	 * @param int             $maxWorkers        Nombre maximum de workers (0 pour no-limit)
	 * @param int             $allowedWSocketsOverflow Nombre de fois que l'on peut augmenter le nombre
	 *                                                 maximum de sockets par worker lorsque le nombre maximum de worker est atteint
	 *                                                 -1 : pas de limite, n : max_wsockets * (n+1)
	 * @param int             $requestTtl        Durée maximum de chaque requête
	 * @param bool            $sendErrorToClient Envoir les erreurs sur les clients socket locale
	 * @throws IllegalInvocation
	 */
	public function __construct(
		string $socketPath,
		int $port,
		ISocketProtocol $protocol,
		IRTSEnvironment $environment,
		int $maxWSocket = 0,
		int $maxWorkers = 0,
		int $allowedWSocketsOverflow = -1,
		int $requestTtl = 900,
		bool $sendErrorToClient = true
	){
		$this->_secretKey = (string) new UUID(UUID::V4);
		$this->_socketPath = $socketPath;
		$this->_protocol = $protocol;
		$this->_environment = $environment;
		$this->_requestTtl = $requestTtl;
		$this->_sendErrorToClient = $sendErrorToClient;
		$this->_port = $port;
		$this->_maxWorkers = $maxWorkers;
		$this->_maxWSockets = $maxWSocket;
		$this->_allowedOverflow = $allowedWSocketsOverflow;
		$this->_workers = [];
		$this->_workersInfos = [];

		//On commence par vérifier l'existence du fichier lock
		//Un seul RTS est autorisé par repertoir de travail.
		$this->_lockFile = $environment->getWorkingDir().DS."server.lock";
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
	}

	public function start():void{
		$sockets =  [];
		socket_create_pair(AF_UNIX,SOCK_STREAM,SOL_TCP,$sockets);
		$this->_localPortPid = pcntl_fork();
		if($this->_localPortPid === 0){
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
			//TODO
			$this->_networkPort = socket_create_listen($this->_port,SOMAXCONN);
			$this->workerManagerLoop();
		}else throw new \Exception("Unable to fork !");
	}

	/**
	 * Crée un nouveau worker
	 * @return int pid du worker créé
	 * @throws MaxWorkerLimitReached
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
				unset($this->_workers[$pid]);
				unset($this->_workersInfos[$pid]);
				socket_close($this->_localPort);
				$this->_environment = null;
			}
			$this->wsLoop();
			return -1;
		}else if($pid > 0){
			socket_close($sockets[0]);
			$this->_workers[$pid] = $sockets[1];
			$this->configureSocket($sockets[1]);
			$this->_workersInfos[$pid] = [
				"clients" => 0
			];
			return $pid;
		}else throw new \Exception("Unable to fork !");
	}

	/**
	 * @throws MaxWorkerLimitReached
	 */
	private function workerManagerLoop():void{
		$this->newWorker();
		while(true){
			$master = [$this->_networkPort];
			$local = [$this->_localPort];
			$chunks = $this->splitIntoChunks(array_merge(
				[$this->_localPort,$this->_networkPort],
				array_values($this->_workers))
			);
			//bypass the 1024 socket management limit
			foreach($chunks as $chunk){
				$read = $chunk; $write = null; $except = null;
				socket_select($read,$write,$except,0);
				if(count(array_intersect($master,$chunk)) === 1){
					$this->accept();
					$read = array_diff($read,$master);
				}
				if(count(array_intersect($read,$local)) === 1){
					//Si la requête du port local n'est pas valide, elle est ignorée.
					$request = json_decode($this->read($this->_localPort));
					if(!is_null($request)) $this->broadCastLocalMessage(new WorkerCommand(
						WorkerCommand::LOCAL,
						"broadcast",
						$request["data"]??'',
						$request["user"]??''
					));
					$read = array_diff($read,$local);
				}
				foreach($read as $s){
					foreach(array_diff($this->_workers,[$s]) as $pid=>$w){
						try{
							while(strlen($wData = $this->read($s))>0){
								if($wData === "connection_closed") $this->_workersInfos[$pid]["clients"]--;
								else $this->write($w,new WorkerCommand(
									WorkerCommand::CLIENT,
									"broadcast",
									$wData
								));
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
		}
	}

	/**
	 * Envoie une commande à tous les workers
	 * @param string $message Message à envoyer à tous les workers
	 */
	private function broadCastLocalMessage(string $message):void{
		foreach($this->_workers as $w){
			$this->write($w,$message);
		}
	}

	/**
	 * Cherche un worker pouvant accepter une nouvelle connection.S'il ne trouve pas, ordonne à un worker
	 * de refuser le nouveau client. (En fait, il l'accepte et lui envoie une erreur avant
	 * de le rejeter)
	 */
	private function accept():void{
		$accepterFound = false;
		foreach($this->_workersInfos as $pid=>$infos){
			if($infos["clients"]<$this->_maxWSockets){
				$this->write($this->_workers[$pid],'{"cmd":"accept_new_client"}');
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
					$this->write($this->_workers[$pid],new WorkerCommand(
						WorkerCommand::ROOT,"accept_new_client"
					));
				else $this->write($this->_workers[$pid],new WorkerCommand(
					WorkerCommand::ROOT,"reject_new_client"
				));
			}
		}
	}

	/**
	 * La fonction socket_select est limitée à 1024 sockets. Donc on fait en sorte de spliter le
	 * tableau en portions de self::MAX_SOCKET_SELECT
	 * @param resource[] $sockets Liste des sockets dans un tableau linéaire
	 * @return resource[] Tableau de chuncks du tableau passé en paramètres.
	 */
	private function splitIntoChunks(array $sockets):array{
		$res = [];
		$current = [];
		$i = 1;
		foreach($sockets as $v){
			if($i%self::MAX_SOCKET_SELECT === 0){
				$current = [];
				$res[] = $current;
			}else $current[]=$v;
			$i++;
		}
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
	 */
	private function wsLoop():void{
		(new RTSNetworkPort(
			$this->_mainProcessSocket,
			$this->_networkPort,
			$this->_environment,
			$this->_protocol
		))->start();
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