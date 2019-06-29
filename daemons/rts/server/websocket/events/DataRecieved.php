<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When data have been recieved
 */
final class DataRecieved extends WebsocketEvent {
	/** @var string $_recieved */
	private $_recieved;

	/**
	 * DataRecieved constructor.
	 *
	 * @param string $socketId
	 * @param string $recieved Data recieved
	 */
	public function __construct(string $socketId,string $recieved) {
		parent::__construct($socketId);
		$this->_recieved = $recieved;
	}

	/**
	 * @return string
	 */
	public function getRecievedData():string{
		return $this->_recieved;
	}
}