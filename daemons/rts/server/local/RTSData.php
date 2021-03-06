<?php

namespace wfw\daemons\rts\server\local;

use wfw\daemons\rts\server\app\events\ClientConnected;
use wfw\daemons\rts\server\app\events\ClientDisconnected;
use wfw\daemons\rts\server\app\events\IRTSAppEvent;

/**
 * Class RTSData
 *
 * @package wfw\daemons\rts\server\local
 */
final class RTSData extends RTSLoggedCommand {
	/** @var IRTSAppEvent[] $_events */
	private $_events;

	/**
	 * RTSData constructor.
	 *
	 * @param string       $sessId
	 * @param IRTSAppEvent ...$events
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $sessId, IRTSAppEvent ...$events) {
		parent::__construct($sessId);
		$this->_events = $events;
		if(count($events) === 0) throw new \InvalidArgumentException(
			"At least one event must be given !"
		);
		foreach($events as $k=>$e){
			if($e instanceof ClientConnected || $e instanceof ClientDisconnected) throw new \InvalidArgumentException(
				"Attempted to send an illegal event (offset $k). ".get_class($e)
				." is only an internal event that cannot be sent to the local port."
			);
		}
	}

	/**
	 * @return IRTSAppEvent[]
	 */
	public function getEvents(): array {
		return $this->_events;
	}
}