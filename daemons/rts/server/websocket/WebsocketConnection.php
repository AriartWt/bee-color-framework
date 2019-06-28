<?php

namespace wfw\daemons\rts\server\websocket;

use wfw\daemons\rts\server\websocket\errors\WebsocketConnectionClosed;
use wfw\daemons\rts\server\websocket\errors\WebsocketIOFailure;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Class WebsocketConnection
 *
 * @package wfw\daemons\rts\server\websocket
 */
final class WebsocketConnection implements IWebsocketConnection {
	/** @var resource $_socket */
	private $_socket;
	/** @var string $_id */
	private $_id;
	/** @var string $_ip */
	private $_ip;
	/** @var int $_port */
	private $_port;
	/** @var bool $_handshaked */
	private $_handshaked;
	/** @var bool $_closed */
	private $_closed;

	/**
	 * WebsocketConnection constructor.
	 *
	 * @param resource $socket The client socket
	 */
	public function __construct($socket) {
		$this->_socket = $socket;
		// set some client-information:
		$socketName = stream_socket_get_name($socket, true);
		$tmp = explode(':', $socketName);
		$this->_ip = $tmp[0];
		$this->_port = (int) $tmp[1];
		$this->_id = (string) new UUID(UUID::V4);
		$this->_handshaked = false;
		$this->_closed = false;
	}

	/**
	 * Recieve client data
	 */
	public function recieve(): void {
		$this->throwIfClosed("receive data");
		$data = $this->read();
	}

	/**
	 * @param string $op Action
	 * @throws WebsocketConnectionClosed
	 */
	private function throwIfClosed(string $op):void{
		if($this->_closed) throw new WebsocketConnectionClosed(
			"Attempting to perform the following action on a closed connection : $op ($this->_id)."
		);
	}

	/**
	 * @return string Data read from socket
	 * @throws WebsocketIOFailure
	 */
	private function read():string{
		$buffer = '';
		$buffsize = 8192;
		$metadata['unread_bytes'] = 0;
		do {
			if (feof($this->_socket)) {
				throw new WebsocketIOFailure(
					"No more data to read from client socket $this->_id. Incomplete message recieved."
				);
			}
			$result = fread($this->_socket, $buffsize);
			if ($result === false || feof($this->_socket)) {
				throw new WebsocketIOFailure("Could not read more data from socket $this->_id");
			}
			$buffer .= $result;
			$metadata = stream_get_meta_data($this->_socket);
			$buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
		} while ($metadata['unread_bytes'] > 0);

		return $buffer;
	}

	/**
	 * Send data to client
	 */
	public function send(): void {
		$this->throwIfClosed("send data");
		// TODO: Implement send() method.
	}

	/**
	 * Close the connection
	 */
	public function close(): void {
		$this->throwIfClosed("close connection");
		// TODO: Implement close() method.
	}

	/**
	 * @return string Connection id
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return string Client IP
	 */
	public function getIp(): string {
		return $this->_ip;
	}

	/**
	 * @return int Client port
	 */
	public function getPort(): int {
		return $this->_port;
	}
}