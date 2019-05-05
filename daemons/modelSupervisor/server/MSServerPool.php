<?php
namespace wfw\daemons\modelSupervisor\server;

use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Gère un pool de serveurs
 */
final class MSServerPool {
	/**
	 *  Timeout des socket sur RCV et SND
	 * @var array $_socketTimeout
	 */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);
	/** @var string $_lockFile */
	private $_lockFile;
	/** @var resource $_acquiredLockFile */
	private $_acquiredLockFile;
	/** @var resource $_socket */
	private $_socket;
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var string $_workingDir */
	private $_workingDir;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var int $_pids */
	private $_pids;
	/** @var string[] $_instancesPath */
	private $_instancesPath;
	/** @var ILogger $_logger */
	private $_logger;
	/** @var int $_aliveCheckerPID */
	private $_aliveCheckerPID;

	/**
	 * MSServerPool constructor.
	 *
	 * @param string          $socketPath     Chemin vers la socket du serveur courant
	 * @param string          $workingDir     Chemin vers le dossier de travail du pool de serveurs
	 * @param ISocketProtocol $protocol       Protocole de communication à utiliser
	 * @param int[]           &$pids          Liste des pids des MSServer à gérer
	 * @param string[]        $instancesPaths Liste des instances sous la forme $name => $socketPath
	 * @param ILogger         $logger         Logger
	 * @param callable        $restartInstance Function that allow to restart an instance.
	 * @throws IllegalInvocation
	 */
	public function __construct(
		string $socketPath,
		string $workingDir,
		ISocketProtocol $protocol,
		array &$pids,
		array $instancesPaths,
		ILogger $logger,
		callable $restartInstance
	){
		$this->_instancesPath = $instancesPaths;
		$this->_logger = $logger;
		$this->_workingDir = $workingDir;
		$this->_socketAddr = $socketPath;
		$this->_protocol = $protocol;
		$this->_pids = $pids;

		if(!is_dir($workingDir)) mkdir($workingDir,0777,true);
		//On commence par vérifier l'existence du fichier lock permettant d'obtenir le lock
		//Un seul MSServer est autorisé par repertoir de travail.
		$this->_lockFile = "$workingDir/server.lock";
		if(!file_exists($this->_lockFile)){
			touch($this->_lockFile);
		}

		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($this->_lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		$this->_socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(file_exists($socketPath)) unlink($socketPath);

		socket_bind($this->_socket,$socketPath);
		socket_listen($this->_socket);

		if($res) throw new IllegalInvocation("A MSServerPool instance is already running for this directory !");
		else file_put_contents("$workingDir/msserver.pid",getmypid());

		$pid = pcntl_fork();
		if($pid < 0) $this->_logger->log(
			"[MSServerPool] Unable to fork to create AliveChecker : maybe insufficient ressources or max process limit reached.",
			ILogger::ERR
		);
		else if($pid === 0){
			$checkInterval = 60; $firstCheck = 60;
			cli_set_process_title("WFW MSServerPool AliveChecker");
			$this->_logger->log(
				"[MSServerPool] [AliveChecker] Started (pid : ".getmypid()
				."). First check in $firstCheck sec. Check interval : $checkInterval sec",
				ILogger::LOG
			);
			sleep($firstCheck);
			while(true){
				foreach($this->_pids as $pid=>$instance){
					$out=[];
					exec("ps -p $pid > /dev/null && echo active || echo inactive",$out);
					if(array_pop($out) === "inactive"){
						$this->_logger->log(
							"[MSServerPool] [AliveChecker] $instance is not running. Trying to restart...",
							ILogger::ERR
						);
						$res = $restartInstance($instance,$pid);
						if($res) exit(0);
						else if(is_null($res)) $this->_logger->log(
							"[MSServerPool] [AliveChecker] Unable to restart instance $instance.",
							ILogger::ERR
						);
						else $this->_logger->log(
							"[MSServerPool] [AliveChecker] Instance $instance successfully restarted",
							ILogger::ERR
						);
					}
				}
				sleep($checkInterval);
			}
		}else $this->_aliveCheckerPID = $pid;
	}

	public function start():void{
		$this->_logger->log("[MSServerPool] Serveur started (pid : ".getmypid().").",ILogger::LOG);
		while(true){
		   try{
			   $socket = socket_accept($this->_socket);
			   $this->configureSocket($socket);
			   $this->process($socket);
		   }catch(\ErrorException $e){
				if(socket_last_error() !== 4) throw $e;
				else exit(0);
		   }
		}
	}

	/**
	 * @param resource $socket Socket à configurer
	 */
	private function configureSocket($socket){
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
	}

	/**
	 * @param resource $socket Socket emettrice d'une requête
	 */
	private function process($socket){
		$this->_logger->log("[MSServerPool] New incoming connection.",ILogger::LOG);
		try{
			$data = $this->read($socket);
			if(isset($this->_instancesPath[$data])){
				$this->write($socket,$this->_instancesPath[$data]);
			}else $this->write($socket,'');
			socket_close($socket);
			$this->_logger->log("[MSServerPool] Response successfully sent.",ILogger::LOG);
		}catch(\Exception $e){
			$errorCode = socket_last_error($socket);
			socket_clear_error($socket);
			$this->_logger->log("[MSServerPool] Can't send response to client : ".print_r([
				"socket_last_error" => [
					"code" => $errorCode,
					"message" =>socket_strerror($errorCode)
				],
				"error" => (string)$e
			],true),ILogger::ERR);
		}
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
	 *  Ecrit des données dans la socket sprécifiée
	 *
	 * @param resource $socket Socket dans laquelle écrire
	 * @param string   $data   Données à écrire
	 */
	private function write($socket,string $data):void{
		$this->_protocol->write($socket,$data);
	}

	/**
	 * Eteint le serveur
	 */
	public function shutdown():void{
		$this->closeConnections();

		flock($this->_acquiredLockFile,LOCK_UN);
		fclose($this->_acquiredLockFile);
		unlink($this->_lockFile);
		if(file_exists($this->_workingDir."/msserver.pid"))
			unlink($this->_workingDir."/msserver.pid");

		posix_kill($this->_aliveCheckerPID,PCNTLSignalsHelper::SIGKILL);
		foreach($this->_pids as $pid=>$instance){
			posix_kill($pid,PCNTLSignalsHelper::SIGALRM);
		}
		$this->_logger->log("[MSServerPool] Gracefull shutdown.",ILogger::LOG);
	}
}