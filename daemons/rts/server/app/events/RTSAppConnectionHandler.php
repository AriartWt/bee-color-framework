<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\app\IRTSApp;
use wfw\daemons\rts\server\app\RTSAppMessage;

/**
 * Class RTSAppEventSubscriber
 *
 * @package wfw\daemons\rts\server\app\events
 */
class RTSAppConnectionHandler implements IRTSAppEventSubscriber {
	private $_clients;

	public function __construct() {
		$this->_clients = [];
	}

	/**
	 * @param string $app
	 * @return ClientConnected[]
	 */
	public function getClients(string $app):array{
		return $this->_clients[$app] ?? [];
	}

	/**
	 * @return string[] eventClass=>method
	 */
	public function getEvents(): array {
		return [
			ClientConnected::class => "clientConnected",
			ClientDisconnected::class => "clientDisconnected"
		];
	}

	/**
	 * @param IRTSApp         $app
	 * @param ClientConnected $event
	 */
	public function clientConnected(IRTSApp $app, ClientConnected $event){
		if(!isset($this->_clients[$event->getConnection()->getApp()]))
			$this->_clients[$event->getConnection()->getApp()] = [];
		$this->_clients[$event->getConnection()->getApp()][$event->getConnection()->getId()] = $event;
		$app->dispatch(
			new RTSAppResponseEvent(
				$event->getConnection()->getId(),
				new RTSAppMessage(
					$event->getConnection()->getApp().":new_connection",
					$event->getConnection()->getId()
				),
				array_keys($this->_clients[$event->getConnection()->getApp()]),
				[$event->getConnection()->getId()]
			)
		);
	}

	/**
	 * @param IRTSApp            $app
	 * @param ClientDisconnected $event
	 */
	public function clientDisconnected(IRTSApp $app, ClientDisconnected $event){
		unset($this->_clients[$event->getConnection()->getApp()][$event->getConnection()->getId()]);
		$app->dispatch(
			new RTSAppResponseEvent(
				$event->getConnection()->getId(),
				new RTSAppMessage(
					$event->getConnection()->getApp().":disconnected",
					$event->getConnection()->getId()
				),
				array_keys($this->_clients[$event->getConnection()->getApp()])
			)
		);
	}
}