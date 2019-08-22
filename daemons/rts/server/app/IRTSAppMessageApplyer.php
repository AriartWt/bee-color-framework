<?php

namespace wfw\daemons\rts\server\app;

/**
 * App message emitter that can handle subscribers
 */
interface IRTSAppMessageApplyer {
	/**
	 * @param IRTSAppMessageSubscriber ...$subscribers Subscribers that will subscribe to messages
	 */
	public function subscribeToAppMessage(IRTSAppMessageSubscriber ...$subscribers):void;

	/**
	 * @param string   $event    Message name to subscribe to
	 * @param callable $callable Callable to call when the message is received
	 */
	public function onMessage(string $event,callable $callable):void;
}