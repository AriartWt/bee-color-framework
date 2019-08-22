<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When an error ocurred on a client socket
 */
final class ErrorOcurred extends WebsocketEvent {
	/** @var \Throwable $_error */
	private $_error;

	/**
	 * ErrorOcurred constructor.
	 *
	 * @param string     $socketId
	 * @param \Throwable $error
	 */
	public function __construct(string $socketId,\Throwable $error) {
		parent::__construct($socketId);
		$this->_error = $error;
	}

	/**
	 * @return \Throwable
	 */
	public function getError():\Throwable{
		return $this->_error;
	}
}