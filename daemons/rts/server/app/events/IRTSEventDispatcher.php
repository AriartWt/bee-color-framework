<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Dispatch RTSEvents
 */
interface IRTSEventDispatcher {
	/**
	 * Send app events to all event listeners
	 *
	 * @param string|null  $appKey    If null, will send to all listeners
	 * @param IRTSEvent    ...$events Event to dispacth
	 */
	public function dispatch(?string $appKey, IRTSEvent ...$events):void;
}