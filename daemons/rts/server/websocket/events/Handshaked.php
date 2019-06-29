<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\IWebsocketSender;
use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When a new connection have been successfully handshaked
 */
final class Handshaked extends WebsocketEvent {
	/** @var IWebsocketSender $_sender */
	private $_sender;
	/** @var string $_app */
	private $_app;

	/**
	 * Handshaked constructor.
	 *
	 * @param string           $socketId
	 * @param IWebsocketSender $sender
	 * @param string           $app
	 */
	public function __construct(string $socketId, IWebsocketSender $sender, string $app) {
		parent::__construct($socketId);
		$this->_sender = $sender;
		$this->_app = $app;
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
	public function getApp(): string {
		return $this->_app;
	}
}