<?php

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\websocket\IWebsocketConnection;
use wfw\daemons\rts\server\websocket\IWebsocketEvent;
use wfw\daemons\rts\server\websocket\IWebsocketListener;
use wfw\daemons\rts\server\websocket\WebsocketConnection;
use wfw\daemons\rts\server\websocket\WebsocketEventObserver;
use wfw\daemons\rts\server\websocket\WebsocketProtocol;
use wfw\daemons\rts\server\worker\WorkerCommand;
use wfw\engine\lib\logger\ILogger;
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
	/** @var IWebsocketConnection[] $_netSocks */
	private $_netSocks;
	/** @var ISocketProtocol $_mainProtocol */
	private $_mainProtocol;
	/** @var int $_sleepInterval */
	private $_sleepInterval;
	/** @var string $_procName */
	private $_procName;
	private $_ipConnections;

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
		$this->_ipConnections = [];
		$this->_procName = $proc = "[".cli_get_process_title()."]";
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
			if ($this->_netSock === false) throw new \RuntimeException("($errno) Unable to create the main socket : $err");
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
		$this->_env->getLogger()->log("$this->_procName start (pid : ".getmypid().")");
		$observer = new WebsocketEventObserver();
		$observer->addEventListener(IWebsocketEvent::class,$this);
		$lastTmpChunkSize = 0;
		$chunks = [];
		while(true){
			$start = microtime(true);
			$changedSocks = [$this->_mainSock];
			$empty = null;
			//accept or reject all client that are waiting on $this->_netSock
			//only if the main worker asks for it
			do{
				socket_select($changedSocks,$empty,$empty,0);
				if(!empty($changedSocks)){
					$data = json_decode($this->_mainProtocol->read($this->_mainSock));
					if(json_last_error() !== JSON_ERROR_NONE) continue;
					$source = $data["source"] ?? null;
					$cmd = $data["cmd"] ?? null;
					$cmdData = $data["data"] ?? '';
					switch($cmd){
						case WorkerCommand::CMD_REJECT:
						case WorkerCommand::CMD_ACCEPT:
							if($source !== WorkerCommand::ROOT){
								$this->_env->getLogger()->log(
									"Command $cmd received from $source socket (ignored)",
									ILogger::ERR
								);
								continue;
							}
							$socket = ($ressource = stream_socket_accept($this->_netSock));
							if(!is_resource($socket)){
								$this->_env->getLogger()->log(
									"Unable to execute command $cmd on accepted socket : stream_socket_accept failed.",
									ILogger::ERR
								);
								continue;
							}
							if($cmd === WorkerCommand::CMD_ACCEPT) $this->addClient(new WebsocketConnection(
								$socket,
								new WebsocketProtocol(),
								$observer,
								$this->_allowedOrigins[],
								$this->_allowedApplications[]
							));
							else $this->addClient(new WebsocketConnection(
								$socket,
								new WebsocketProtocol(),
								$observer,
								$this->_allowedOrigins[],
								$this->_allowedApplications[],
								503,
								$cmdData
							));
							break;
						case WorkerCommand::CMD_BROADCAST :
							foreach($this->_netSocks as $connection){
								if(!$connection->isClosed()) $connection->send($data);
							}
							break;
						default :
							break;
					}
				}
			}while(!empty($changedSocks));

			//check with socket select, no blocking, no timeout, that no message was found on mainSocket
			//If there are, read, parse and see
			if(count($this->_netSocks) !== $lastTmpChunkSize)
				$chunks = $this->splitIntoChunks($this->_netSocks);

			foreach($chunks as $chunk){
				$ready = $chunk;
				$empty = null;
				stream_select($ready,$empty,$empty,0);
				foreach($ready as $socket){
					if(isset($this->_netSocks[(int)$socket])) $this->_netSocks[(int)$socket]->recieve();
					else{
						$this->_env->getLogger()->log(
							"Unable to find socket ".((int)$socket)." connection object.",
							ILogger::ERR
						);
					}
				}
			}

			//cleanup closed connections, in case something goes wrong
			foreach($this->_netSocks as $k=>$connection) if($connection->isClosed())
				$this->removeClient($this->_netSocks[$k]);

			//allow the process to not wait if many tasks was done, but to wait and let other process
			//run if nothing or little to do.
			$execTime = microtime(true) - $start;
			if( $execTime < $this->_sleepInterval) usleep($execTime - $start);
		}
	}

	/**
	 * @param IWebsocketConnection $connection Connection to add to server connections
	 */
	private function addClient(IWebsocketConnection $connection):void{
		$this->_env->getLogger()->log(
			"$this->_procName New client "
			.$connection->getId()." created ( IP : ".$connection->getIp()." )"
		);
		$this->_netSocks[(int)$connection->getSocket()] = $connection;
		if(!isset($this->_ipConnections[$connection->getIp()]))
			$this->_ipConnections[$connection->getIp()] = [];
		$this->_ipConnections[$connection->getIp()][$connection->getId()] = $connection;
	}

	/**
	 * @param IWebsocketConnection $connection Connection to remove from server connections
	 */
	private function removeClient(IWebsocketConnection $connection):void{
		$this->_env->getLogger()->log(
			"$this->_procName Client ".$connection->getId()." removed."
		);
		if(isset($this->_netSocks[(int)$connection->getSocket()]))
			unset($this->_netSocks[(int)$connection->getSocket()]);

		if(isset($this->_ipConnections[$connection->getIp()])
			&& isset($this->_ipConnections[$connection->getIp()][$connection->getId()]))
			unset($this->_ipConnections[$connection->getIp()][$connection->getId()]);
	}

	/**
	 * La fonction socket_select est limitée à 1024 sockets. Donc on fait en sorte de spliter le
	 * tableau en portions de self::MAX_SOCKET_SELECT
	 * @param IWebsocketConnection[] &$sockets Liste des sockets dans un tableau linéaire
	 * @return resource[] Tableau de chuncks du tableau passé en paramètres.
	 */
	private function splitIntoChunks(array &$sockets):array{
		$res = [];
		$current = [];
		$i = 1;
		foreach($sockets as $v){
			if(!$v->isClosed()) $current[] = $v->getSocket();
			if($i%RTS::MAX_SOCKET_SELECT === 0){
				$res[] = $current;
				$current = [];
			}
			$i++;
		}
		if(!empty($current)) $res[] = $current;
		return $res;
	}

	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public function apply(IWebsocketEvent $event): void {
		// TODO: Implement apply() method.
	}
}