<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Dispatch RTSEvents
 */
interface IRTSEventDispatcher {
	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSEvent    ...$events Event to dispacth
	 */
	public function dispatch(IRTSEvent ...$events):void;
}