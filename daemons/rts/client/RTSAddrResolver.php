<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 09:35
 */

namespace wfw\daemons\rts\client;

use wfw\daemons\multiProcWorker\socket\protocol\DefaultProtocol;
use wfw\daemons\rts\errors\RTSFailure;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Résoud l'addresse d'une instance de RTSServer en communiquant avec le RTSPool
 */
final class RTSAddrResolver implements IRTSAddrResolver{
	/** @var string $_addr */
	private $_addr;
	/** @var ISocketProtocol $_protocol */
	private $_protocol;
	/** @var array $_socketTimeout */
	private $_socketTimeout = array("sec"=>2,"usec"=>0);

	/**
	 * MSInstanceAddrResolver constructor.
	 * @param string          $msserverAddr Chemin d'accés à la socket d'écoute du MSServerPool
	 * @param null|ISocketProtocol $protocol Par défaut, utilise le MSServerSocketProtocol
	 */
	public function __construct(string $msserverAddr,?ISocketProtocol $protocol=null) {
		$this->_addr = $msserverAddr;
		$this->_protocol = $protocol ?? new DefaultProtocol();
	}

	/**
	 * Permet de retrouver l'adresse de la socket d'écoute d'une instance de MSServer
	 *
	 * @param string $name Nom de l'instance du MSServer
	 * @return string
	 */
	public function find(string $name): string {
		$socket = socket_create(AF_UNIX,SOCK_STREAM,0);
		$this->configureSocket($socket);
		socket_connect($socket,$this->_addr);
		$this->_protocol->write($socket,$name);
		$res = $this->_protocol->read($socket);
		socket_close($socket);
		if(strlen($res)===0)
			throw new RTSFailure("Unable to resolve $name : unknown instance");
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