<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 08:38
 */

namespace wfw\daemons\rts\server;

use Exception;
use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Port local
 */
final class RTSLocalPort {
	/** @var resource $_mainProcessSocket */
	private $_mainProcessSocket;
	/** @var resource $_localSocket */
	private $_localSocket;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var bool $_sendErrorToClient */
	private $_sendErrorToClient;
	/** @var IRTSEnvironment $_environment */
	private $_environment;
	/** @var int $_requestTtl */
	private $_requestTtl;
	/** @var string $_lockFile */
	private $_lockFile;
	/** @var bool|resource $_acquiredLockFile */
	private $_acquiredLockFile;
	/** @var string $_socketAddr */
	private $_socketAddr;
	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>10,"usec"=>0);
	/** @var string $_procName */
	private $_procName;

	/**
	 * RTSLocalPort constructor.
	 *
	 * @param string          $sockAddr          Nom de la socket du rtsLocalPort
	 * @param string          $workingDir        Dossier de travail pour le fichier lock
	 * @param resource        $socket            Socket de communication
	 * @param ISocketProtocol $protocol          Protocole de communication pour les sockets locales
	 * @param IRTSEnvironment $environment       Environement de travail.
	 * @param int             $requestTtl        Temps de vie des requêtes
	 * @param bool            $sendErrorToClient Envoie l'erreur à un client local, si une erreur survient.
	 * @throws IllegalInvocation
	 */
	public function __construct(
		string $sockAddr,
		string $workingDir,
		$socket,
		ISocketProtocol $protocol,
		IRTSEnvironment $environment,
		int $requestTtl = 900,
		bool $sendErrorToClient = true
	){
		$this->_procName = cli_get_process_title();
		$this->_mainProcessSocket = $socket;
		$this->_protocol = $protocol;
		$this->_environment = $environment;
		$this->_sendErrorToClient = $sendErrorToClient;
		$this->_requestTtl = $requestTtl;
		$this->_socketAddr = $sockAddr;

		//On commence par vérifier l'existence du fichier lock
		//Un seul RTSLocalPort est autorisé par repertoir de travail.
		$this->_lockFile = $workingDir."/local_port.lock";
		if(!file_exists($this->_lockFile)){
			touch($this->_lockFile);
		}

		//On vérifie qu'on peut acquérir le lock.
		$fp = fopen($this->_lockFile,"r+");
		$res = !flock($fp,LOCK_NB | LOCK_EX);
		$this->_acquiredLockFile = $fp;

		$this->_localSocket = socket_create(AF_UNIX,SOCK_STREAM,0);
		if(file_exists($sockAddr)){
			unlink($sockAddr);
		}
		socket_bind($this->_localSocket,$sockAddr);
		socket_listen($this->_localSocket);

		if($res) throw new IllegalInvocation(
			"A RTS instance is already running for this directory !"
		);
	}

	/**
	 * Handle only one-shot connections.
	 */
	public function start(){
		while(true){
			$socket= socket_accept($this->_localSocket);
			$this->configureSocket($socket);
			$this->process($socket);
		}
	}

	/**
	 * @param resource $socket Socket à traiter
	 */
	private function process($socket):void{
		try{
			$data = $this->read($socket);
			if(strlen($data)>0){
				$data = json_decode($data,true);
				if(is_null($data)) $this->sendError($socket,"Invalid JSON string !");
				else{
					if(!isset($data["cmd"])) $this->sendError($socket,"No cmd specified !");
					else $this->processCommand($socket,$data);
				}
			}else $this->sendError($socket,"No data recieved !");
		}catch(\Exception | \Error $e){
			$this->sendError($socket,$e);
			$this->_environment->getLogger()->log(
				"Error while trying to execute client request : $e",
				ILogger::ERR
			);
		}
		socket_close($socket);
	}

	/**
	 * Traite une commande de la requête
	 * @param resource $socket Socket
	 * @param array $data
	 */
	private function processCommand($socket, array $data){
		$this->_environment->destroyOutdatedSessions();
		$cmd = $data["cmd"];
		switch($cmd){
			case "data" :
				if(is_string($data['sessid']??null)){
					if($this->checkAuth($socket,$data["sessid"])){
						$this->_environment->touchUserSession($data["sessid"]);
						try{
							$user = $this->_environment->getUserSession($data["sessid"])->getUser();
							$this->write(
								$this->_mainProcessSocket,
								'{"data":"'.$data["data"].'","user":"'.$user->getName().'"}'
							);
							$this->write($socket,'sent');
						}catch(\Exception | \Error $e){
							$this->sendError($socket,$e);
						}
					}
				}else $this->sendError($socket,"Field sessid have to be defined !");
				break;
			case "login" :
				if(is_string($data['login']??null) && is_string($data['password']??null)){
					$sessId = $this->_environment->createSessionForUser(
						$data['login'],
						$data['password']
					);
					if(is_null($sessId)) $this->sendError(
						$socket,"Wrong login/password !","rejected"
					);
					else $this->write($socket,'{"sessid":"'.$sessId.'"}');
				} else $this->sendError(
					$socket,"Fields login and password have to be defined !","data"
				);
				break;
			case "logout" :
				if(is_string($data['sessid']??null)){
					if($this->checkAuth($socket,$data["sessid"])){
						$this->_environment->destroyUserSession($data['sessid']);
						$this->_protocol->write($socket,"disconnected");
					}
				}else $this->sendError($socket,"Field sessid have to be defined !");
				break;
			default :
				$this->sendError($socket,"Unknown command $cmd","command");
				break;
		}
	}

	/**
	 * Permet de verifier qu'un utilisateur est authentifié pour une action.
	 * @param resource $socket Socket sur laquelle renvoyer l'erreur, s'il y en a une
	 * @param string $sessId Identifiant de session
	 * @return bool
	 */
	private function checkAuth($socket,string $sessId):bool{
		if(!$this->_environment->existsUserSession($sessId)){
			$this->sendError($socket,"Unknown session, or session timed out","rejected");
			return false;
		}
		return true;
	}

	/**
	 * @param resource $socket
	 * @param string   $error
	 * @param string   $type
	 */
	private function sendError($socket, string $error,string $type="general"){
		if($this->_sendErrorToClient) $this->write(
			$socket,'{"error":"'.$error.'","type":"'.$type.'"}'
		);
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
		if(!is_null($this->_localSocket)){
			socket_close($this->_localSocket);
			if(file_exists($this->_socketAddr)) unlink($this->_socketAddr);
		}
		socket_close($this->_mainProcessSocket);

		if(!is_null($e)){
			$this->_environment->getLogger()->log(
				"$this->_procName An error caused the local port to shutdown : $e",
				ILogger::ERR
			);
			exit(1);
		}else exit(0);
	}
}