<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Allow you to register a list of listeners for an event, and to dispatch all events on registered
 * listeners.
 */
final class RTSAppEventEmitter implements IRTSAppEventEmitter, IRTSAppEventDispatcher{
	/** @var IRTSAppEventListener[][] $_listeners */
	private $_listeners;

	public function __construct() {
		$this->_listeners = [];
	}

	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSAppEvent ...$events Event to dispacth
	 */
	public function dispatch(IRTSAppEvent ...$events): void {
		$map = [];
		foreach($events as $e){
			foreach($this->_listeners as $class=>$listeners){
				if(!isset($map[$class])) $map[$class] = ["events"=>[],"listeners"=>[]];
				if(is_a($e,$class)){
					$map[$class]["events"][] = $e;
					foreach($listeners as $listener) $map[$class]["listeners"][] = $listener;
				}
			}
		}
		foreach($map as $item){
			foreach($item["listeners"] as $listener){
				/** @var IRTSAppEventListener $listener */
				$listener->applyRTSEvents(...$item["events"]);
			}
		}
	}

	/**
	 * Subscribe to the listeners of the IRTSAppEvent emitted
	 *
	 * @param string               $eventClass
	 * @param IRTSAppEventListener $listener
	 */
	public function subscribeToAppEmitter(string $eventClass, IRTSAppEventListener $listener): void {
		if(!isset($this->_listeners[$eventClass])) $this->_listeners[$eventClass] = [];
		$this->_listeners[$eventClass][] = $listener;
	}
}