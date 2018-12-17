<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Observer d'événements websocket de base.
 */
final class WebsocketEventObserver implements IWebsocketEventObserver {
	/** @var IWebsocketListener[][] $_listeners */
	private $_listeners;

	/**
	 * WebsocketEventObserver constructor.
	 *
	 * @param IWebsocketListener[][] $listeners Liste de listeners
	 */
	public function __construct(array $listeners = []) {
		$this->_listeners = [];
		foreach($listeners as $k=>$v){
			if(is_string($k)){
				$this->_listeners[$k]=[];
				foreach($v as $listener){
					if($listener instanceof IWebsocketListener) $this->_listeners[$k][]=$listener;
				}
			}
		}
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
	public function removeEventListener(?IWebsocketListener $listener=null, ?string $event = null) {
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

	/**
	 * Appelle les listeners correspondants aux événements.
	 * @param IWebsocketEvent ...$events Evenement à dispatcher
	 */
	public function dispatch(IWebsocketEvent... $events):void {
		foreach($events as $event){
			if(isset($this->_listeners[$event->getName()])){
				foreach($this->_listeners[$event->getName()] as $listener){
					$listener->apply($event);
				}
			}
		}
	}

	/**
	 * @param null|string ...$events Liste des événements dont on souhaite obtenir les listeners
	 * @return IWebsocketListener[][]
	 */
	public function getListeners(?string... $events): array {
		if(count($events)===0) return $this->_listeners;
		else{
			$res =[];
			foreach($events as $e){
				$res[$e] = $this->_listeners[$e] ?? [];
			}
			return $res;
		}
	}
}