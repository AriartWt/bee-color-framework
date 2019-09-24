<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When data was sent to client
 */
final class DataSent extends WebsocketEvent {
	/** @var string $_data */
	private $_data;

	/**
	 * DataSent constructor.
	 *
	 * @param string $socketId
	 * @param string $data
	 */
	public function __construct(string $socketId, string $data) {
		parent::__construct($socketId);
		$this->_data = $data;
	}

	/**
	 * @return string
	 */
	public function getData(): string {
		return $this->_data;
	}
}