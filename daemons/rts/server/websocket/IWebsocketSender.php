<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Allow to send data on a websocket connection
 */
interface IWebsocketSender {
	/**
	 * @param string $data Data to send
	 * @param string $type
	 */
	public function send(string $data, string $type = IWebsocketProtocol::TEXT);
}