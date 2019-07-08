<?php

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\app\events\ClientConnected;
use wfw\daemons\rts\server\app\events\ClientDisconnected;
use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppResponseEvent;
use wfw\daemons\rts\server\app\events\RTSAppError;
use wfw\daemons\rts\server\app\events\RTSCloseConnectionsEvent;
use wfw\daemons\rts\server\app\IRTSAppsManager;
use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\websocket\events\Accepted;
use wfw\daemons\rts\server\websocket\events\Closed;
use wfw\daemons\rts\server\websocket\events\DataRecieved;
use wfw\daemons\rts\server\websocket\events\DataSent;
use wfw\daemons\rts\server\websocket\events\ErrorOcurred;
use wfw\daemons\rts\server\websocket\events\Handshaked;
use wfw\daemons\rts\server\websocket\IWebsocketConnection;
use wfw\daemons\rts\server\websocket\IWebsocketEvent;
use wfw\daemons\rts\server\websocket\IWebsocketEventObserver;
use wfw\daemons\rts\server\websocket\IWebsocketListener;
use wfw\daemons\rts\server\websocket\IWebsocketProtocol;
use wfw\daemons\rts\server\websocket\WebsocketConnection;
use wfw\daemons\rts\server\websocket\WebsocketEventObserver;
use wfw\daemons\rts\server\websocket\WebsocketProtocol;
use wfw\daemons\rts\server\worker\InternalCommand;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Network port able to accept/read/write into websockets
 */
final class RTSNetworkPort implements IWebsocketListener{
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
	/** @var string $_logHead */
	private $_logHead;
	/** @var string[] $_socketIds */
	private $_socketIds;
	/** @var IRTSAppsManager $_appsManager */
	private $_appsManager;
	/** @var int $_maxMainSocketRead */
	private $_maxMainSocketRead;
	/** @var string $_rootKey */
	private $_rootKey;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var array $_ipCount */
	private $_ipCount;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param string          $host
	 * @param int             $port
	 * @param resource        $mainSocket Socket de communication avec le processus principal
	 * @param IRTSEnvironment $env        Environnement RTS
	 * @param ISocketProtocol $mainProtocol
	 * @param IRTSAppsManager $appsManager
	 * @param ISerializer     $serializer
	 * @param string          $rootKey
	 * @param null|resource   $netSocket
	 * @param int             $sleepInterval
	 * @param string          $logHead
	 * @throws \RuntimeException
	 */
	public function __construct(
		string $host,
		int $port,
		$mainSocket,
		IRTSEnvironment $env,
		ISocketProtocol $mainProtocol,
		IRTSAppsManager $appsManager,
		ISerializer $serializer,
		string $rootKey,
		$netSocket = null,
		int $sleepInterval = 100,
		string $logHead = "[RTS] [NetworkPort]"
	) {
		$this->_ipCount = [];
		$this->_serializer = $serializer;
		$this->_maxMainSocketRead = 20;
		$this->_appsManager = $appsManager;
		$this->_socketIds = [];
		$this->_logHead = "$logHead [".getmypid()."]";
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
			if ($this->_netSock === false) throw new \RuntimeException(
				"($errno) Unable to create the main socket : $err"
			);
		}

		$this->_env = $env;
		$this->_netSocks = [];
		$this->_rootKey = $rootKey;
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
		$this->_env->getLogger()->log("$this->_logHead start (pid : ".getmypid().")");
		$observer = new WebsocketEventObserver();
		$observer->addEventListener(IWebsocketEvent::class,$this);
		$lastTmpChunkSize = 0;
		$chunks = [];
		while(true){
			$start = microtime(true);
			$changedSocks = [$this->_mainSock];
			$empty = null;
			$mainSocketRead = 0;
			//accept or reject all client that are waiting on $this->_netSock
			//only if the main worker asks for it
			do{
				socket_select($changedSocks,$empty,$empty,0);
				if(!empty($changedSocks)){
					try{
						$decoded = $this->_serializer->unserialize(
							$this->_mainProtocol->read($this->_mainSock)
						);
						if($decoded instanceof InternalCommand){
							$this->processCommand($decoded,$observer);
						}else $this->_env->getLogger()->log(
							"$this->_logHead ".InternalCommand::class." was expected but "
							.gettype($decoded)." recieved. (ignored)",
							ILogger::ERR
						);
					}catch(\Error | \Exception $e){
						$this->_env->getLogger()->log(
							"$this->_logHead Unable to decode root command : $e",
							ILogger::ERR
						);
					}
				}
				$mainSocketRead ++;
			}while(!empty($changedSocks) && $mainSocketRead <= $this->_maxMainSocketRead);

			if(count($this->_netSocks) !== $lastTmpChunkSize){
				$chunks = $this->splitIntoChunks($this->_netSocks);
				$lastTmpChunkSize = count($this->_netSocks);
			}

			foreach($chunks as $chunk){
				$ready = $chunk;
				$empty = null;
				stream_select($ready,$empty,$empty,0);
				foreach($ready as $socket){
					if(isset($this->_netSocks[(string)(int)$socket])) $this->_netSocks[(string)(int)$socket]->recieve();
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
	 * @param InternalCommand         $decoded
	 * @param IWebsocketEventObserver $observer
	 */
	private function processCommand(InternalCommand $decoded,IWebsocketEventObserver $observer):void{
		$source = $decoded->getSource();
		$cmd = $decoded->getName();
		$cmdData = $decoded->getData();
		$rootKey = $decoded->getRootKey();
		switch($cmd){
			case InternalCommand::CMD_REJECT:
			case InternalCommand::CMD_ACCEPT:
				if($source !== InternalCommand::ROOT){
					$this->_env->getLogger()->log(
						"Command $cmd received from $source socket (ignored)",
						ILogger::ERR
					);
					return;
				}
				$socket = ($ressource = stream_socket_accept($this->_netSock));
				if(!is_resource($socket)){
					$this->_env->getLogger()->log(
						"Unable to execute command $cmd on accepted socket : stream_socket_accept failed.",
						ILogger::ERR
					);
					return;
				}
				if($cmd === InternalCommand::CMD_ACCEPT) $this->addClient(new WebsocketConnection(
					$socket,
					new WebsocketProtocol(),
					$observer,
					$this->_env->getMaxReadBufferSize(),
					$this->_env->getMaxWriteBufferSize(),
					$this->_env->getMaxRequestHandshakeSize(),
					$this->_env->getMaxRequestBySecondByClient(),
					$this->_env->getAllowedOrigins(),
					$this->_appsManager->getAppNames(),
					null,
					null,
					$this->reachedIpCount()
				));
				else $this->addClient(new WebsocketConnection(
					$socket,
					new WebsocketProtocol(),
					$observer,
					$this->_env->getMaxReadBufferSize(),
					$this->_env->getMaxWriteBufferSize(),
					$this->_env->getMaxRequestHandshakeSize(),
					$this->_env->getMaxRequestBySecondByClient(),
					$this->_env->getAllowedOrigins(),
					$this->_appsManager->getAppNames(),
					503,
					$cmdData
				));
				break;
			case InternalCommand::DATA_TRANSMISSION:
				if($source !== InternalCommand::ROOT) {
					$this->_env->getLogger()->log(
						"$this->_logHead Command $cmd recieved from $source (ignored)",
						ILogger::WARN
					);
					return;
				}
				if($rootKey !== $this->_rootKey){
					$this->_env->getLogger()->log(
						"$this->_logHead Invalid root key given for $cmd from $source (ignored)",
						ILogger::ERR
					);
					return;
				}
				if(!is_array($cmdData)){
					$this->_env->getLogger()->log(
						"$this->_logHead Invalid command data from $source. "
						."An array was expected (ignored).",
						ILogger::ERR
					);
					return;
				}
				try{
					/** @var IRTSAppResponseEvent[] $responses */
					$responses = [];
					/** @var RTSCloseConnectionsEvent[] $closes */
					$closes = [];
					$dispatches = [];
					foreach($cmdData as $event){
						if($event instanceof ClientConnected)
							$this->addToIpCount($event->getConnection()->getIp());
						else if($event instanceof ClientDisconnected)
							$this->removeFromIpCount($event->getConnection()->getIp());
						if($event instanceof IRTSAppResponseEvent){
							if($event instanceof RTSCloseConnectionsEvent) $closes[] = $event;
							else $responses[] = $event;
						}else $dispatches[] = $event;
					}
					foreach($responses as $response){
						foreach($this->findClients($response) as $client) $client->send(
							$response->getData(), IWebsocketProtocol::TEXT,true
						);
					}
					foreach($closes as $close){
						foreach($this->findClients($close) as $client) $client->close(
							IWebsocketProtocol::STATUS_NORMAL_CLOSE
						);
					}
					$this->_appsManager->dispatch(...$dispatches);
				}catch(\Error | \Exception $e){
					$this->_env->getLogger()->log(
						"An error occured while trying to dispatch $source command events : $e",
						ILogger::ERR
					);
				}
				break;
			default :
				break;
		}
	}

	/**
	 * @param IRTSAppResponseEvent $event
	 * @return IWebsocketConnection[]
	 */
	private function findClients(IRTSAppResponseEvent $event):array{
		$recepts = array_flip($event->getRecipients() ?? []);
		$excepts = array_flip($event->getExcepts());
		$clients = [];
		if(is_null($event->getRecipients())) $clients = $this->_netSocks;
		else foreach($this->_netSocks as $sock){
			if(isset($recepts[$sock->getId()]) && !isset($excepts[$sock->getId()]))
				$clients[$sock->getId()] = $sock;
		}
		return $clients;
	}

	/**
	 * @param string $ip
	 */
	private function addToIpCount(string $ip):void{
		if(!isset($this->_ipCount[$ip])) $this->_ipCount[$ip]=1;
		else $this->_ipCount[$ip]++;
	}

	/**
	 * @param string $ip
	 */
	private function removeFromIpCount(string $ip):void{
		if(isset($this->_ipCount[$ip])){
			$this->_ipCount[$ip]--;
			if($this->_ipCount[$ip] <= 0) unset($this->_ipCount[$ip]);
		}
	}

	/**
	 * @return array
	 */
	private function reachedIpCount():array{
		$maxConByIp = $this->_env->getMaxConnectionsByIp();
		if($maxConByIp <= 0) return [];
		$res = [];
		foreach($this->_ipCount as $ip=>$n){
			if($n >= $maxConByIp) $res[$ip] = true;
		}
		return $res;
	}

	/**
	 * @param IWebsocketConnection $connection Connection to add to server connections
	 */
	private function addClient(IWebsocketConnection $connection):void{
		$this->_env->getLogger()->log(
			"$this->_logHead New client "
			.$connection->getId()." created ( IP : ".$connection->getIp()." )"
		);
		$this->_netSocks[(string)(int)$connection->getSocket()] = $connection;
		$this->_socketIds[$connection->getId()] = (string)(int)$connection->getSocket();
		$this->addToIpCount($connection->getIp());
	}

	/**
	 * @param IWebsocketConnection $connection Connection to remove from server connections
	 */
	private function removeClient(IWebsocketConnection $connection):void{
		if(isset($this->_netSocks[(string)(int)$connection->getSocket()]))
			unset($this->_netSocks[(string)(int)$connection->getSocket()]);

		if(isset($this->_socketIds[$connection->getId()]))
			unset($this->_netSocks[$connection->getId()]);

		$this->removeFromIpCount($connection->getIp());

		$this->_env->getLogger()->log(
			"$this->_logHead Client ".$connection->getId()." removed."
		);
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
			if($i % RTS::MAX_SOCKET_SELECT === 0){
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
	public function applyWebsocketEvent(IWebsocketEvent $event): void {
		try{
			if($event instanceof Handshaked){
				$this->_appsManager->dispatch($e = new ClientConnected(
					$event->getConnectionInfos(),
					$event->getCreationDate()
				));
				$this->_mainProtocol->write($this->_mainSock,$this->_serializer->serialize(new InternalCommand(
					InternalCommand::WORKER,
					InternalCommand::DATA_TRANSMISSION,
					$this->_serializer->serialize([$e]),
					$event->getConnectionInfos()->getId(),
					$this->_rootKey
				)));
			}else if($event instanceof Closed){
				$this->_appsManager->dispatch($e = new ClientDisconnected(
					$event->getConnectionInfos(),
					$event->getCreationDate(),
					$event->getMessage(),
					$event->getCode()
				));
				$this->_mainProtocol->write($this->_mainSock,$this->_serializer->serialize(new InternalCommand(
					InternalCommand::WORKER,
					InternalCommand::DATA_TRANSMISSION,
					$this->_serializer->serialize([$e]),
					$event->getConnectionInfos()->getId(),
					$this->_rootKey
				)));
			}else if($event instanceof ErrorOcurred){
				if(isset($this->_netSocks[$event->getSocketId()])) $this->_appsManager->dispatch(
					new RTSAppError(
						$this->_netSocks[$event->getSocketId()],$event->getError()
					)
				);
				$this->_env->getLogger()->log(
					"$this->_logHead An error occured on connection ".$event->getSocketId()
					." : ".$event->getError(),
					ILogger::ERR
				);
			}else if($event instanceof DataSent){
				$this->_env->getLogger()->log(
					"$this->_logHead Data successfully sent to client ".$event->getSocketId()
				);
			}else if($event instanceof DataRecieved){
				$connection = $this->_netSocks[$this->_socketIds[$event->getSocketId()]] ?? null;
				if($connection){
					$this->_env->getLogger()->log(
						"$this->_logHead Data recieved from ".$connection->getId()." (app : "
						.$connection->getApp().")"
					);
					$events = $this->filterResponseEvents(...$this->_appsManager->dispatchData(
						$connection->getApp(),
						$event->getRecievedData()
					));
					$localEvents = [];
					$mustBeSentEvents = [];
					foreach($events as $e){
						if($e->distributionMode(IRTSAppEvent::SCOPE)){
							$localEvents[] = $e;
							if($e instanceof IRTSAppResponseEvent) foreach($this->findClients($e) as $client){
								if($e instanceof RTSCloseConnectionsEvent) $client->close(
									IWebsocketProtocol::STATUS_NORMAL_CLOSE
								);
								else $client->send(
									$e->getData(),IWebsocketProtocol::TEXT,true
								);
							}
						}
						if($e->distributionMode(IRTSAppEvent::CENTRALIZATION)
							|| $e->distributionMode(IRTSAppEvent::DISTRIBUTION)){
							if(!($e instanceof IRTSAppResponseEvent)
								|| count($this->findClients($e)) < count($e->getRecipients())){
								$mustBeSentEvents[] = $e;//do not send events if all clients are in the current worker
							}
						}
					}
					if(!empty($localEvents)) $this->_appsManager->dispatch(...$localEvents);
					$this->_mainProtocol->write($this->_mainSock,$this->_serializer->serialize(new InternalCommand(
						InternalCommand::WORKER,
						InternalCommand::DATA_TRANSMISSION,
						$this->_serializer->serialize($mustBeSentEvents)
					)));
				}else $this->_env->getLogger()->log(
					"Unable to find connection ".$event->getSocketId(),
					ILogger::ERR
				);
			}else if($event instanceof Accepted){
				$this->_env->getLogger()->log(
					"New client accepted : id -> ".$event->getSocketId().", IP -> ".$event->getIp()
					.", Port -> ".$event->getPort()
				);
			}else $this->_env->getLogger()->log(
				"Unable to handle event ".get_class($event),
				ILogger::WARN
			);
		}catch(\Error | \Exception $e){
			$this->_env->getLogger()->log(
				"$this->_logHead An error occured while trying to apply event "
				.get_class($event)." : $e",
				ILogger::ERR
			);
		}
	}

	/**
	 *
	 * @param IRTSAppEvent[] $events
	 * @return IRTSAppEvent[]
	 */
	private function filterResponseEvents(IRTSAppEvent ...$events):array{
		$keysToRemove = [];
		foreach($events as $k=>$e) if($e instanceof IRTSAppResponseEvent){
			if(!is_null($e->getRecipients())){
				$notInCurrentWorker = array_diff_key(
					array_flip($e->getRecipients()),$this->_socketIds
				);
				if(count($notInCurrentWorker) === 0) $keysToRemove[$k] = true;
				foreach($e->getRecipients() as $clientId){
					if(isset($this->_netSocks[$this->_socketIds[$clientId]])) {
						try {
							$this->_netSocks[$this->_socketIds[$clientId]]->send(
								$e->getData(),
								IWebsocketProtocol::TEXT,
								true
							);
						} catch (\Error | \Exception $e) {
							$this->_env->getLogger()->log(
								"$this->_logHead Unable to write in client socket $clientId : $e",
								ILogger::ERR
							);
						}
					}
				}
			}
		}
		return array_values(array_diff_key($events,$keysToRemove));
	}
}