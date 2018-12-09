<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Observer d'événements websocket de base.
 */
final class WebsocketEventObserver implements IWebsocketEventObserver {
	/** @var IWebsocketListener[] $_listeners */
	private $_listeners;

	public function __construct() {
		$this->_listeners = [];
	}

	/**
	 * @param string             $event    Evenement à écouter
	 * @param IWebsocketListener $listener Ecouteur
	 * @return mixed|void
	 */
	public function addEventListener(string $event, IWebsocketListener $listener) {
		if(!isset($this->_listeners[$event])) $this->_listeners[$event]=[];
		$this->_listeners[$event][]=$listener;
	}

	/**
	 * @param IWebsocketListener $listener
	 * @param null|string        $event
	 * @return mixed|void
	 */
	public function removeEventListener(IWebsocketListener $listener, ?string $event = null) {
		$removeFrom=[];
		if($event) $removeFrom[$event]=$this->_listeners[$event]??[];
		else $removeFrom = $this->_listeners;
		foreach($removeFrom as $event=>$listeners){
			foreach($listeners as $k=>$listener){
				array_splice($removeFrom[$event],$k,1);
			}
			$this->_listeners[$event]=$removeFrom[$event];
		}
	}

	public function dispatch(IWebsocketEvent $event) {
		// TODO: Implement dispatch() method.
	}

	/**
	 * @param null|string ...$events Liste des événements dont on souhaite obtenir les listeners
	 * @return IWebsocketListener[][]
	 */
	public function getListeners(?string... $events): array {
		// TODO: Implement getListeners() method.
	}
}