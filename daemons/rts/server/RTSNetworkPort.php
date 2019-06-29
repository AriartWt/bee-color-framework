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
	private $_sleepInterval;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param string          $host
	 * @param int             $port
	 * @param resource        $mainSocket Socket de communication avec le processus principal
	 * @param IRTSEnvironment $env        Environnement RTS
	 * @param ISocketProtocol $mainProtocol
	 * @param null|resource   $netSocket
	 * @param int             $sleepInterval
	 * @throws \RuntimeException
	 */
	public function __construct(
		string $host,
		int $port,
		$mainSocket,
		IRTSEnvironment $env,
		ISocketProtocol $mainProtocol,
		$netSocket = null,
		int $sleepInterval = 100
	) {
		$this->_mainSock = $mainSocket;
		if(is_null($netSocket)){
			$url = "tcp://$host:$port";
			$this->_netSock = stream_socket_server(
				$url,
				$errno,
				$err,
				STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
				stream_context_create()
			);
			if ($this->_netSock === false) throw new \RuntimeException("Error creating socket: $err");
		}

		$this->_env = $env;
		$this->_netSocks = [];
		$this->_mainProtocol = $mainProtocol;
		$this->_sleepInterval = $sleepInterval;
	}

	/**
	 * @return resource
	 */
	public function getNetworkSocket(){
		return $this->_netSock;
	}

	public function start():void{
		$observer = new WebsocketEventObserver();
		$observer->addEventListener(IWebsocketEvent::class,$this);
		while(true){
			$start = microtime(true);
			//check with socket select, no blocking, no timeout, that no message was found on mainSocket
			//If there are, read, parse and see
			//Then, checking each connexion with socket stream select and process sockets.
			//will only ACCEPT NEW SOCKETS on NETSOCK if accept_new_client is recieved on MAINSOCK
			$execTime = microtime(true) - $start;
			if( $execTime < $this->_sleepInterval) usleep($execTime - $start);
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
			if($i%RTS::MAX_SOCKET_SELECT === 0){
				$current = [];
				$res[] = $current;
			}else $current[]=$v;
			$i++;
		}
		return $res;
	}

	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public function apply(IWebsocketEvent $event): void {
		// TODO: Implement apply() method.
	}
}