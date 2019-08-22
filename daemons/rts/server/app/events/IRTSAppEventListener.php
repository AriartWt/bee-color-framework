<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * React to RTS events
 */
interface IRTSAppEventListener {
	/**
	 * @param IRTSAppEvent[] $events
	 */
	public function applyRTSEvents(IRTSAppEvent ...$events);
}