<?php

namespace wfw\daemons\rts\server\websocket\errors;

use Throwable;

/**
 * Throwed when failing de decode data recieved on a websocket
 */
class WebsocketProtocolFailure extends WebsocketFailure {

	/** @var int $_status */
	private $_status;

	/**
	 * WebsocketDataDecodingFailure constructor.
	 *
	 * @param int            $status
	 * @param null|string    $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct(int $status, ?string $message = null, int $code = 0, Throwable $previous = null) {
		parent::__construct("Unable to encode/decode websocket data".($message ? " : $message" : ""), $code, $previous);
		$this->_status = $status;
	}

	/**
	 * @return int
	 */
	public function getStatus():int{
		return $this->_status;
	}
}