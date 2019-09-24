<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * For object that are able to construct and send IRTSAppEvent
 */
interface IRTSAppEventEmitter {
	/**
	 * Add a listener for the events. Listeners will recieve events when they're created.
	 * @param string               $eventClass
	 * @param IRTSAppEventListener $listener
	 */
	public function subscribeToAppEmitter(string $eventClass, IRTSAppEventListener $listener) : void;
}