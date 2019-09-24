<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Dispatch RTSEvents
 */
interface IRTSAppEventDispatcher {
	/**
	 * Send app events to all event listeners
	 *
	 * @param IRTSAppEvent ...$events Event to dispacth
	 */
	public function dispatch(IRTSAppEvent ...$events):void;
}