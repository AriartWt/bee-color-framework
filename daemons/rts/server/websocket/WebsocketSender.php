<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Class WebsocketSender
 *
 * @package wfw\daemons\rts\server\websocket
 */
final class WebsocketSender implements IWebsocketSender {
	/** @var IWebsocketConnection $_connection */
	private $_connection;

	/**
	 * WebsocketSender constructor.
	 *
	 * @param IWebsocketConnection $connection
	 */
	public function __construct(IWebsocketConnection $connection) {
		$this->_connection = $connection;
	}

	/**
	 * @param string $data Data to send
	 * @param string $type
	 */
	public function send(string $data,string $type = IWebsocketProtocol::TEXT) {
		$this->_connection->send($data,$type,true);
	}
}