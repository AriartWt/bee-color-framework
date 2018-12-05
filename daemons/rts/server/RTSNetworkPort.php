<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 14:48
 */

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Port network permettant de gérer les clients websocket.
 */
final class RTSNetworkPort{
	/** @var resource $_mainSock */
	private $_mainSock;
	/** @var resource $_netSock */
	private $_netSock;
	/** @var IRTSEnvironment $_env */
	private $_env;
	private $_netSocks;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param resource        $mainSocket Socket de communication avec le processus principal
	 * @param resource        $netSocket  Port network (reception des websockets)
	 * @param IRTSEnvironment $env        Environnement RTS
	 * @param ISocketProtocol $protocol   Protocole de communication avec $mainSocket
	 */
	public function __construct(
		$mainSocket,
		$netSocket,
		IRTSEnvironment $env,
		ISocketProtocol $protocol
	) {
		$this->_mainSock = $mainSocket;
		$this->_netSock = $netSocket;
		$this->_env = $env;
		$this->_netSocks = [];
	}

	public function start():void{

	}
}