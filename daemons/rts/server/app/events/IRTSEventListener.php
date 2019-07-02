<?php

namespace wfw\daemons\rts\server\app\events;

/**
 * React to RTS events
 */
interface IRTSEventListener {
	/**
	 * @param IRTSEvent[] $event
	 */
	public function applyRTSEvents(IRTSEvent ...$event);
}