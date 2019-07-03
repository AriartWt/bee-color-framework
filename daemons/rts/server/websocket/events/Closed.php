<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\IWebsocketConnection;
use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * Client connexion closed
 */
final class Closed extends WebsocketEvent {
	/** @var int $_code */
	private $_code;
	/** @var string $_message */
	private $_message;
	/** @var string $_connectionInfos */
	private $_connectionInfos;

	/**
	 * Closed constructor.
	 *
	 * @param IWebsocketConnection $connection
	 * @param int                  $closeCode
	 * @param string               $message
	 */
	public function __construct(IWebsocketConnection $connection,int $closeCode, string $message = "") {
		parent::__construct($connection->getId());
		$this->_code = $closeCode;
		$this->_message = $message;
		$this->_connectionInfos = json_encode($connection);
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

	/**
	 * @return string
	 */
	public function getConnectionInfos():string{
		return $this->_connectionInfos;
	}
}