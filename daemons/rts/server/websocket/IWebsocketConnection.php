<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Websocket connection to a client
 */
interface IWebsocketConnection {
	/**
	 * Recieve data from client
	 */
	public function recieve():void;

	/**
	 * Send data to client
	 *
	 * @param string $payload Payload to send to client
	 * @param string $type    Type of response
	 * @param bool   $masked  Response mask
	 * @return bool True if message sent, false otherwise
	 */
	public function send(string $payload, string $type, bool $masked = false):bool;

	/**
	 * Close connection
	 *
	 * @param int $status
	 */
	public function close(int $status = IWebsocketProtocol::STATUS_NORMAL_CLOSE):void;

	/**
	 * @return string Connection id
	 */
	public function getId():string;

	/**
	 * @return string Client IP
	 */
	public function getIp():string;

	/**
	 * @return int Client port
	 */
	public function getPort():int;
}