<?php

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\websocket\IWebsocketEvent;
use wfw\daemons\rts\server\websocket\IWebsocketListener;
use wfw\daemons\rts\server\websocket\WebsocketEventObserver;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Network port able to accept/read/write into websockets
 */
final class RTSNetworkPort implements IWebsocketListener {
	/** @const int $max_client 1024 is the max with select(), we keep space for rejecting socket */
	protected const MAX_SOCKET_SELECT = 1000;
	/** @var resource $_mainSock */
	private $_mainSock;
	/** @var resource $_netSock */
	private $_netSock;
	/** @var IRTSEnvironment $_env */
	private $_env;
	/** @var array $_netSocks */
	private $_netSocks;
	/** @var ISocketProtocol $_mainProtocol */
	private $_mainProtocol;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param resource           $mainSocket Socket de communication avec le processus principal
	 * @param resource           $netSocket  Port network (reception des websockets)
	 * @param IRTSEnvironment    $env        Environnement RTS
	 * @param ISocketProtocol    $mainProtocol
	 */
	public function __construct(
		$mainSocket,
		$netSocket,
		IRTSEnvironment $env,
		ISocketProtocol $mainProtocol
	) {
		$this->_mainSock = $mainSocket;
		$this->_netSock = $netSocket;
		$this->_env = $env;
		$this->_netSocks = [];
		$this->_mainProtocol = $mainProtocol;
	}

	public function start():void{
		$observer = new WebsocketEventObserver();
		$observer->addEventListener(IWebsocketEvent::class,$this);
		while(true){
			sleep(1);
			//check with socket select, no blocking, no timeout, that no message was found on mainSocket
			//If there are, read, parse and see
			//Then, checking each connexion with socket stream select and process sockets.
			//will only ACCEPT NEW SOCKETS on NETSOCK if accept_new_client is recieved on MAINSOCK
		}
	}

	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public function apply(IWebsocketEvent $event): void {
		// TODO: Implement apply() method.
	}
}