<?php
namespace wfw\daemons\multiProcWorker;

use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Permet de lancer un worker. Crée un environnement propice à l'IPC grâce à pcntl_fork et
 * socket_create_pair
 */
abstract class Worker {
	public const CLIENT_MODE=1;
	public const WORKER_MODE=0;

	/** @var int $_pid */
	private $_pid;
	/** @var int $_mode */
	private $_mode;
	/** @var resource $_socket */
	protected $_socket;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var string $_errorLog */
	private $_errorLog;
	/** @var bool $_ipc */
	private $_ipc;
	/** @var bool $_exitWorkerAfterRun */
	private $_exitWorkerAfterRun;
	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);

	/**
	 * Worker2 constructor.
	 *
	 * @param ISocketProtocol $protocol           Protocole de communication entre père et fils
	 * @param string          $errorLog           Log d'erreurs
	 * @param bool            $exitWorkerAfterRun exit(0) après l'appel à runWorker()
	 * @param bool            $enableIpc          Si true, crée une paire de socket pour l'IPC
	 */
	public function __construct(
		ISocketProtocol $protocol,
		string $errorLog,
		bool $exitWorkerAfterRun = true,
		bool $enableIpc = true
	){
		$this->_mode = self::CLIENT_MODE;
		$this->_protocol = $protocol;
		$this->_errorLog = $errorLog;
		$this->_exitWorkerAfterRun = $exitWorkerAfterRun;
		$this->_ipc = $enableIpc;
	}

	/**
	 * Lancé seulement par le worker
	 */
	protected abstract function runWorker():void;

	/**
	 * Lancé seulement par le client
	 */
	protected abstract function runClient():void;

	/**
	 *  Fonction à appeler pour démarrer le worker..
	 *
	 * @param bool $runClient Autorise la routine client à s'executer
	 * @throws \Exception
	 */
	public final function start(bool $runClient=true):void{
		if(!$this->_pid){
			$sockets = $this->initIPC();
			$this->_pid = pcntl_fork();
			if($this->_pid === 0){
				$this->_mode = self::WORKER_MODE;
				$this->_pid = getmypid();
				$this->setUpIPC($sockets);
				//exec the worker routine
				$this->runWorker();
				if($this->_exitWorkerAfterRun) exit(0);
			}else if($this->_pid > 0){
				$this->setUpIPC($sockets);
				if($runClient) $this->runClient();
			}else throw new \Exception("Unable to fork :(");
		}
	}

	/**
	 * @return array Vide si ipc desactivé, contenant deux sockets sinon
	 */
	private function initIPC():array{
		$sockets = [];
		if($this->_ipc){
			if (socket_create_pair(AF_UNIX, SOCK_STREAM, 0,$sockets) === false)
				$this->errorLog("socket_create_pair failed : ".socket_strerror(socket_last_error()));
			return $sockets;
		}
		return $sockets;
	}

	/**
	 * @param resource[] $sockets
	 */
	private function setUpIPC(array $sockets):void{
		if($this->_ipc){
			if($this->_mode === self::CLIENT_MODE){
				socket_close($sockets[1]);
				$this->_socket = $sockets[0];
				$this->configureSocket($this->_socket);
			}else{
				socket_close($sockets[0]);
				$this->_socket = $sockets[1];
				$this->configureSocket($this->_socket);
			}
		}
	}

	/**
	 *  Configure une socket
	 * @param resource $socket Socket à configurer
	 */
	protected function configureSocket($socket){
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
	}

	/**
	 * @param null|resource $socket Lis la socket fourni, _socket sinon
	 * @return string Lit des données sur la socket
	 */
	protected function read($socket=null):string{
		return $this->_protocol->read($socket ?? $this->_socket);
	}

	/**
	 * @param string $data Ecrit des données dans la socket
	 * @param null|resource   $socket Ecrit dans la socjet fournie, _socket sinon
	 */
	protected function write(string $data,$socket=null):void{
		$this->_protocol->write($socket ?? $this->_socket,$data);
	}

	/**
	 * Ferme la connexion de la socket
	 */
	protected function close():void{
		socket_close($this->_socket);
	}

	/**
	 * @return int
	 */
	public function getWorkerPid():int{
		return $this->_pid;
	}

	/**
	 * @return int
	 */
	public function getWorkerMode():int{
		return $this->_mode;
	}

	/**
	 * @param string $message Message d'erreur à enregistrer
	 */
	protected function errorLog(string $message){
		error_log(
			"$this->_pid (".static::class."): $message\n",
			3,
			$this->_errorLog
		);
	}
}