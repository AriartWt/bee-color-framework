<?php

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\websocket\IWebsocketEvent;
use wfw\daemons\rts\server\websocket\IWebsocketListener;
use wfw\daemons\rts\server\websocket\IWebsocketProtocol;
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
	/** @var IWebsocketProtocol $_wsProtocol */
	private $_wsProtocol;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param resource           $mainSocket Socket de communication avec le processus principal
	 * @param resource           $netSocket  Port network (reception des websockets)
	 * @param IRTSEnvironment    $env        Environnement RTS
	 * @param ISocketProtocol    $mainProtocol
	 * @param IWebsocketProtocol $wsProtocol
	 */
	public function __construct(
		$mainSocket,
		$netSocket,
		IRTSEnvironment $env,
		ISocketProtocol $mainProtocol,
		IWebsocketProtocol $wsProtocol
	) {
		$this->_mainSock = $mainSocket;
		$this->_netSock = $netSocket;
		$this->_env = $env;
		$this->_netSocks = [];
		$this->_mainProtocol = $mainProtocol;
		$this->_wsProtocol = $wsProtocol;
	}

	public function start():void{
		while(true){
			sleep(1);
			//check main read/write
			//check net read/write
			//check all users

			//la classe RTSPort doit servir d'intialiseur pour l'application temps réel.
			//Ce qui veut dire qu'elle va souscrire aux événements de la websocket :
			// connect/disconnet/connected/disconnected/message/needread/needwrite...
			// Si elle reçoit un type "message" elle va déclencher l'interface correspondante
			//C'est elle qui met en place l'eventloop, du coup. Il y aura deux RTSNetworkPort :
			//un normal, et un basé sur libevent, quand le premier fonctionnera

			/*
			 * Ensuite, elle sert de proxy pour le listener de l'application, et répliquera certains
			 * événements (connect, disconnect, message...) mais pas d'autres, qui concernent plus
			 * le fonctionnement spécifique des websocket (needWrite, needRead...)
			 *
			 * WebsocketListener accepte un WebsocketEvent, mais il doit être différent de l'observer
			 * passé à NetworkPort.
			 * Dans l'idéal, RTSPort va accepter en arguments une liste de IRTSApp
			 * chaque interface disposera de méthodes ayant la même signature qu'en javascript,
			 * pour des raisons de cohérence.
			 *
			 * En parallèle, le networkPort écoute aussi le LocalPort pour pouvoir broadcaster des messages
			 * Le LocalPort est géré par un process dédié, et c'est par son intermédiaire que le RTS
			 * demandera au networkPort d'accepter/rejeter une connexion, ou qu'il enverra une demande
			 * de broadcast.
			 *
			 * La seule différence, c'est qu'on proposera un MessageHandler avec un WebsocketObserver
			 * permettant de sauter l'étape
			 * */
		}
	}

	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public function apply(IWebsocketEvent $event): void {
		// TODO: Implement apply() method.
	}
}