<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * Client connexion closed
 */
final class Closed extends WebsocketEvent {
	/** @var int $_code */
	private $_code;
	/** @var string $_message */
	private $_message;

	/**
	 * Closed constructor.
	 *
	 * @param string $socketId
	 * @param int    $closeCode
	 * @param string $message
	 */
	public function __construct(string $socketId,int $closeCode, string $message = "") {
		parent::__construct($socketId);
		$this->_code = $closeCode;
		$this->_message = $message;
	}

	/**
	 * @return int
	 */
	public function getCode():int{
		return $this->_code;
	}

	/**
	 * @return string
	 */
	public function getMessage():string{
		return $this->_message;
	}
}