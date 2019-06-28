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
	 */
	public function send():void;

	/**
	 * Close connection
	 */
	public function close():void;

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