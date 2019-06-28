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
	/** @var IWebsocketProtocol $_protocol */
	private $_protocol;

	/**
	 * WebsocketConnection constructor.
	 *
	 * @param resource           $socket The client socket
	 * @param IWebsocketProtocol $protocol
	 */
	public function __construct($socket, IWebsocketProtocol $protocol) {
		$this->_socket = $socket;
		$this->_protocol = $protocol;

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
	 *
	 * @param string $payload
	 * @param string $type
	 * @param bool   $masked
	 * @return bool True if message sent, false otherwise
	 * @throws WebsocketConnectionClosed
	 */
	public function send(string $payload, string $type, bool $masked = false): bool {
		$this->throwIfClosed("send data");
		$this->write($this->_protocol->encode($payload, $type, $masked));
	}

	/**
	 * @param string $data data to write in client socket
	 * @return bool|int
	 */
	public function write(string $data){
		$stringLength = strlen($data);
		if ($stringLength === 0) {
			return 0;
		}

		for ($written = 0; $written < $stringLength; $written += $fwrite) {
			$fwrite = @fwrite($this->_socket, substr($data, $written));
			if ($fwrite === false) {
				throw new WebsocketIOFailure('Could not write to stream.');
			}//TODO : better error message
			if ($fwrite === 0) {
				throw new WebsocketIOFailure('Could not write to stream.');
			}
		}

		return $written;
	}

	/**
	 * Close the connection
	 *
	 * @param int $statusCode
	 * @throws WebsocketConnectionClosed
	 */
	public function close(int $statusCode = IWebsocketProtocol::STATUS_NORMAL_CLOSE): void {
		$this->throwIfClosed("close connection");
		$payload = str_split(sprintf('%016b', $statusCode), 8);
		$payload[0] = chr(bindec($payload[0]));
		$payload[1] = chr(bindec($payload[1]));
		$payload = implode('', $payload);

		switch ($statusCode) {
			case IWebsocketProtocol::STATUS_NORMAL_CLOSE:
				$payload .= 'normal closure';
				break;
			case IWebsocketProtocol::STATUS_GOING_AWAY:
				$payload .= 'going away';
				break;
			case IWebsocketProtocol::STATUS_PROTOCOL_ERROR:
				$payload .= 'protocol error';
				break;
			case IWebsocketProtocol::STATUS_UNKNOWN_DATA:
				$payload .= 'unknown data (opcode)';
				break;
			case IWebsocketProtocol::STATUS_MESSAGE_TOO_LARGE:
				$payload .= 'frame too large';
				break;
			case IWebsocketProtocol::STATUS_INCONSISTENT_DATA:
				$payload .= 'utf8 expected';
				break;
			case IWebsocketProtocol::STATUS_MESSAGE_VIOLATES_SERVER_POLICY:
				$payload .= 'message violates server policy';
				break;
			default :
				$payload .= 'Unknown error';
		}
		$this->send($payload, 'close', false);
		stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
		$this->_closed = true;
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