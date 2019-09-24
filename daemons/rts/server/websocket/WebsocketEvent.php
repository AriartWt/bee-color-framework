<?php

namespace wfw\daemons\rts\server\websocket;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Websocket event emitted by a Websocket connection
 */
abstract class WebsocketEvent implements IWebsocketEvent{
	/** @var string $_id */
	private $_id;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var string $_socketId */
	private $_socketId;

	/**
	 * WebsocketEvent constructor.
	 *
	 * @param string $socketId
	 */
	public function __construct(string $socketId) {
		$this->_id = (string) new UUID(UUID::V4);
		$this->_creationDate = microtime(true);
		$this->_socketId = $socketId;
	}

	/**
	 * @return string Event Id
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return float event creation date in microseconds
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * @return string
	 */
	public function getSocketId(): string {
		return $this->_socketId;
	}
}