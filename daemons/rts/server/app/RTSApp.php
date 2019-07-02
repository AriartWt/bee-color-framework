<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSEvent;

/**
 * Base rts class
 */
class RTSApp implements IRTSApp{
	private $_listeners;

	public function __construct() {
		$this->_listeners = [];
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
}