<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * React to RTS events
 */
interface IRTSAppEventListener {
	/**
	 * @param IRTSAppEvent[] $event
	 */
	public function applyRTSEvents(IRTSAppEvent ...$event);
}