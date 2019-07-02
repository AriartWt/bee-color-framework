<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\IWebsocketConnection;
use wfw\daemons\rts\server\websocket\IWebsocketSender;
use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When a new connection have been successfully handshaked
 */
final class Handshaked extends WebsocketEvent {
	/** @var IWebsocketSender $_sender */
	private $_sender;
	/** @var string $_connection */
	private $_connection;

	/**
	 * Handshaked constructor.
	 *
	 * @param string               $socketId
	 * @param IWebsocketSender     $sender
	 * @param IWebsocketConnection $connection
	 */
	public function __construct(
		string $socketId,
		IWebsocketSender $sender,
		IWebsocketConnection $connection
	) {
		parent::__construct($socketId);
		$this->_sender = $sender;
		$this->_connection = json_encode($connection);
	}

	/**
	 * @return IWebsocketSender
	 */
	public function getSender(): IWebsocketSender {
		return $this->_sender;
	}

	/**
	 * @return string
	 */
	public function getConnectionInfos(): string {
		return $this->_connection;
	}
}