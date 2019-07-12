<?php

namespace wfw\daemons\rts\server\app;

/**
 * Subscriber to clients messages.
 * All methods defined must accept the current IRTSApp instance as first argument,
 * and the client data as second argument.
 */
interface IRTSAppMessageSubscriber {
	/**
	 * @return string[] eventName => method
	 */
	public function getEvents():array;
}