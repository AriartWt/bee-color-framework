<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * Allow a subscriber to subscribe to one or more RTSAppEvents and bind methods to events.
 */
interface IRTSAppEventSubscriber {
	/**
	 * @return string[] eventClass=>method
	 */
	public function getEvents():array;
}