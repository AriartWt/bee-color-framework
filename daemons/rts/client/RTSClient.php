<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 09:15
 */

namespace wfw\daemons\rts\client;

use wfw\daemons\multiProcWorker\socket\protocol\DefaultProtocol;
use wfw\daemons\rts\client\errors\MustBeLogged;
use wfw\daemons\rts\client\errors\RTSClientFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Client RTS
 */
class RTSClient implements IRTSClient{
	/** @var string $_addr */
	private $_addr;
	/** @var string $_login */
	private $_login;
	/** @var string $_password */
	private $_password;
	/** @var null|ISocketProtocol $_protocol */
	private $_protocol;
	/** @var null|string $_sessId */
	private $_sessId;
	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>15,"usec"=>0);

	/**
	 * RTSClient constructor.
	 *
	 * @param string               $addr     Addr socket du RTS
	 * @param string               $login    Login
	 * @param string               $password Mot de passe
	 * @param null|ISocketProtocol $protocol Protocol de communication intersocket
	 */
	public function __construct(
		string $addr,
		string $login,
		string $password,
		?ISocketProtocol $protocol = null
	){
		$this->_addr = $addr;
		$this->_login = $login;
		$this->_password = $password;
		$this->_protocol = $protocol ?? new DefaultProtocol();
	}

	/**
	 * Broadcast un événement sur le RTS
	 * @param string $event Nom de l'évent
	 * @param string $data  Données de l'event
	 */
	public function broadcast(string $event, string $data): void {
		$res = $this->sendRequest([
			"sessid" => $this->_sessId,
			"data" => json_encode(["event" => $event,"data" => $data]),
			"cmd" => "broadcast"
		]);
		if($res !== "broadcasted") $this->parseError($res);
	}

	/**
	 * Se connecte à une instance RTS
	 */
	public function login(): void {
		$res = $this->sendRequest([
			"cmd"=>"login",
			"login"=>$this->_login,
			"password"=>$this->_password
		]);
		$parsed = json_decode($res,true);
		if(is_null($parsed) || !isset($parsed["sessid"])) $this->parseError($res);
		else $this->_sessId = $parsed["sessid"];
	}

	/**
	 * Se déconnecte d'une instance RTS
	 * @throws MustBeLogged
	 */
	public function logout(): void {
		if(is_null($this->_sessId))
			throw new MustBeLogged("You must le logged before trying to logout !");
		$res = $this->sendRequest(["sessid"=>$this->_sessId,"cmd"=>"logout"]);
		if($res !== "disconnected") $this->parseError($res);
	}

	/**
	 * @param string $error
	 * @param string $exceptClass Classe de l'exception à lever.
	 */
	public function parseError(string $error,string $exceptClass = RTSClientFailure::class){
		$res = json_decode($error);
		if(is_null($res)) throw new $exceptClass($error);
		else throw new $exceptClass("Type : ".($res['type']??'unknown')." \n Error : ".$res["error"]);
	}

	/**
	 * @param array $req Envoie la requête $req et renvoie la réponse du serveur
	 * @return string
	 */
	private function sendRequest(array $req):string{
		$socket = $this->createClientSocket();
		$this->_protocol->write($socket,json_encode($req));
		return $this->_protocol->read($socket);
	}

	/**
	 *  Crée une socket et la paramètre avec le timeout défini par $this->_socketTiemout
	 * @return resource
	 */
	private function createClientSocket(){
		$socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		$this->configureSocket($socket);
		socket_connect($socket,$this->_addr);
		return $socket;
	}

	/**
	 *  Configure une socket
	 * @param resource $socket Socket à configurer
	 */
	private function configureSocket($socket){
		socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,$this->_socketTimeout);
		socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,$this->_socketTimeout);
	}

	/**
	 * @return bool True si le client est actuellement loggé (ie a obtenu un sessid)
	 */
	public function isLogged(): bool {
		return !is_null($this->_sessId);
	}
}