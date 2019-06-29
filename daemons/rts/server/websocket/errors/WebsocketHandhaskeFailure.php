<?php

namespace wfw\daemons\rts\server\websocket\errors;

use Throwable;

/**
 * When the websocket handhaske fails
 */
class WebsocketHandhaskeFailure extends WebsocketFailure {
	/** @var int $_httpCode */
	private $_httpCode;

	/**
	 * WebsocketHandhaskeFailure constructor.
	 *
	 * @param int            $httpCode
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct(int $httpCode, string $message = "", int $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->_httpCode = $httpCode;
	}

	/**
	 * @return int
	 */
	public function getHttpCode(): int {
		return $this->_httpCode;
	}


}