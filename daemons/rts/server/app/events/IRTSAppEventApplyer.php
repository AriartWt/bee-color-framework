<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Interface IRTSAppEventApplyer
 *
 * @package wfw\daemons\rts\server\app\events
 */
interface IRTSAppEventApplyer {
	/**
	 * Subscribe to IRTSAppEvent applyed.
	 *
	 * @param IRTSAppEventSubscriber[] $subscribers
	 */
	public function subscribeToAppEvents(IRTSAppEventSubscriber ...$subscribers):void;

	/**
	 * Add a subscriber
	 * @param string   $class
	 * @param callable $callable
	 */
	public function onEvent(string $class, callable $callable) : void;
}