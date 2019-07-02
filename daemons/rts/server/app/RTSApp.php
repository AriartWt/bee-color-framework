<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEvent;
use wfw\daemons\rts\server\app\events\IRTSEventListener;
use wfw\daemons\rts\server\app\events\IRTSEventObserver;
use wfw\daemons\rts\server\app\events\RTSEventObserver;

/**
 * Base rts class
 */
class RTSApp implements IRTSApp{
	/** @var null|IRTSEventObserver|RTSEventObserver $_observer */
	private $_observer;
	/** @var array $_listeners */
	private $_listeners;

	/**
	 * RTSApp constructor.
	 *
	 * @param null|IRTSEventObserver $observer
	 */
	public function __construct(?IRTSEventObserver $observer=null) {
		$this->_listeners = [];
		$this->_observer = $observer ?? new RTSEventObserver();
	}

	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 *
	 * @return string The app key
	 */
	public function getKey(): string {
		return '*';
	}

	/**
	 * @param string   $event
	 * @param callable $listener
	 */
	protected function on(string $event, callable $listener):void{
		if(!class_exists($event) || !interface_exists($event))
			throw new \InvalidArgumentException("Class or interface not found : $event");
		if(!isset($this->_listeners[$event])) $this->_listeners[$event] = [];
		$this->_listeners[$event][] = $listener;
	}

	/**
	 * @param string $data
	 * @return IRTSEvent[]
	 */
	public function receiveData(string $data): array {
		// TODO: Implement receiveData() method.
	}

	/**
	 * @param IRTSEvent[] $event
	 */
	public function applyRTSEvents(IRTSEvent ...$event) {
		// TODO: Implement applyRTSEvents() method.
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		// TODO: Implement getId() method.
	}

	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSEvent ...$events Event to dispacth
	 */
	public function dispatch(IRTSEvent ...$events): void {
		$this->_observer->dispatch(...$events);
	}

	/**
	 * @param null|string $appKey If given, return only listeners registered for an appKey
	 * @return IRTSEventListener[] Return all listeners
	 */
	public function getListeners(?string $appKey): array {
		return $this->_observer->getListeners($appKey);
	}

	/**
	 * @param string|null       $appKey       If null, will add listeners to all keys
	 * @param IRTSEventListener ...$listeners Listeners that listen to IRTSEvents
	 */
	public function addListeners(?string $appKey, IRTSEventListener ...$listeners): void {
		$this->_observer->addListeners($appKey,...$listeners);
	}

	/**
	 * @param null|string       $appKey       If null, will remove listeners from all keys
	 * @param IRTSEventListener ...$listeners Listeners to remove
	 */
	public function removeListeners(?string $appKey, IRTSEventListener ...$listeners): void {
		$this->_observer->removeListeners($appKey,...$listeners);
	}

	/**
	 * Remove all listeners
	 *
	 * @param null|string $appKey If given, remove all listeners for a key
	 */
	public function removeAllListeners(?string $appKey): void {
		$this->_observer->removeAllListeners($appKey);
	}
}