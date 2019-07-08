<?php

namespace wfw\daemons\rts\server\websocket;

use wfw\daemons\rts\server\websocket\errors\InvalidWebsocketConnection;
use wfw\daemons\rts\server\websocket\errors\WebsocketConnectionClosed;
use wfw\daemons\rts\server\websocket\errors\WebsocketHandshakeFailure;
use wfw\daemons\rts\server\websocket\errors\WebsocketIOFailure;
use wfw\daemons\rts\server\websocket\events\Accepted;
use wfw\daemons\rts\server\websocket\events\Closed;
use wfw\daemons\rts\server\websocket\events\DataRecieved;
use wfw\daemons\rts\server\websocket\events\DataSent;
use wfw\daemons\rts\server\websocket\events\ErrorOcurred;
use wfw\daemons\rts\server\websocket\events\Handshaked;
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
	/** @var string $_app */
	private $_app;
	/** @var int $_port */
	private $_port;
	/** @var array[][] */
	private $_queue;
	/** @var string[] $_headers */
	private $_headers;
	/** @var bool $_handshaked */
	private $_handshaked;
	/** @var bool $_closed */
	private $_closed;
	/** @var IWebsocketProtocol $_protocol */
	private $_protocol;
	/** @var IWebsocketEventDispatcher $_dispatcher */
	private $_dispatcher;
	/** @var string $_dataBuffer */
	private $_dataBuffer;
	/** @var bool $_waitingForData */
	private $_waitingForData;
	/** @var array $_allowedOrigins */
	private $_allowedOrigins;
	/** @var array $_allowedApplications */
	private $_allowedApplications;
	/** @var ?int $_rejectOnHandshakeCode */
	private $_rejectOnHandshakeCode;
	/** @var null|string $_rejectOnHandshakeMessage */
	private $_rejectOnHandshakeMessage;
	/** @var bool $_unserialized */
	private $_unserialized;
	/** @var int $_maxReadBufferSize */
	private $_maxReadBufferSize;
	/** @var int $_maxWriteBufferSize */
	private $_maxWriteBufferSize;
	/** @var int $_maxRequestHandshakeSize */
	private $_maxRequestHandshakeSize;
	/** @var int $_maxRequestByMinute */
	private $_maxRequestByMinute;
	/** @var float|int $_requestsCounterDate */
	private $_requestsCounterDate;
	/** @var int $_requestsCounter */
	private $_requestsCounter;
	/** @var array $_rejectIps */
	private $_rejectIps;

	/**
	 * WebsocketConnection constructor.
	 *
	 * @param resource                  $socket The client socket
	 * @param IWebsocketProtocol        $protocol
	 * @param IWebsocketEventDispatcher $dispatcher
	 * @param int                       $maxReadBufferSize
	 * @param int                       $maxWriteBufferSize
	 * @param int                       $maxRequestHandshakeSize
	 * @param int                       $maxRequestsByMinute
	 * @param array                     $allowedOrigins
	 * @param array                     $allowedApplications
	 * @param int|null                  $rejectOnHandshakeCode
	 * @param null|string               $rejectOnHandshakeMessage
	 * @param array                     $rejectIps
	 */
	public function __construct(
		$socket,
		IWebsocketProtocol $protocol,
		IWebsocketEventDispatcher $dispatcher,
		int $maxReadBufferSize = 49152,
		int $maxWriteBufferSize = 49152,
		int $maxRequestHandshakeSize = 1024,
		int $maxRequestsByMinute = 20,
		array $allowedOrigins = [],
		array $allowedApplications = [],
		?int $rejectOnHandshakeCode = null,
		?string $rejectOnHandshakeMessage = "Server busy",
		array $rejectIps = []
	){
		$this->_queue = [];
		$this->_socket = $socket;
		$this->_requestsCounter = 0;
		$this->_requestsCounterDate = time() * 1000;
		$this->_maxReadBufferSize = $maxReadBufferSize;
		$this->_maxWriteBufferSize = $maxWriteBufferSize;
		$this->_maxRequestByMinute = $maxRequestsByMinute;
		$this->_maxRequestHandshakeSize = $maxRequestHandshakeSize;
		$this->_rejectOnHandshakeMessage = $rejectOnHandshakeMessage;
		$this->_rejectOnHandshakeCode = $rejectOnHandshakeCode;
		$this->_protocol = $protocol;
		$this->_rejectIps = $rejectIps;
		$this->_dispatcher = $dispatcher;
		$this->_allowedOrigins = (function(string ...$origins){
			$res = [];
			foreach($origins as $domain){
				$domain = str_replace('http://', '', $domain);
				$domain = str_replace('https://', '', $domain);
				$domain = str_replace('www.', '', $domain);
				$domain = (strpos($domain, '/') !== false) ? substr($domain, 0, strpos($domain, '/')) : $domain;
				if (!empty($domain)) {
					$res[$domain] = true;
				}
			}
			return $origins;
		})(...$allowedOrigins);
		$this->_allowedApplications = (function(string ...$apps){
			return array_flip($apps);
		})(...$allowedApplications);

		$socketName = stream_socket_get_name($socket, true);
		$tmp = explode(':', $socketName);
		$this->_ip = $tmp[0];
		$this->_port = (int) $tmp[1];
		$this->_id = (string) new UUID(UUID::V4);

		$this->_dataBuffer = '';
		$this->_closed = false;
		$this->_handshaked = false;
		$this->_waitingForData = false;
		$this->_unserialized = false;

		$dispatcher->dispatch(new Accepted(
			$this->_id,
			$this->_ip,
			$this->_port
		));
	}

	/**
	 * Checks if the submitted origin (part of websocket handshake) is allowed
	 * to connect.
	 *
	 * @param string $domain The origin-domain from websocket handshake.
	 * @return bool If domain is allowed to connect method returns true.
	 */
	private function checkOrigin(string $domain): bool {
		if(empty($this->_allowedOrigins)) return true;

		$domain = str_replace('http://', '', $domain);
		$domain = str_replace('https://', '', $domain);
		$domain = str_replace('www.', '', $domain);
		$domain = str_replace('/', '', $domain);

		return isset($this->allowedOrigins[$domain]);
	}

	/**
	 * Check if an app exists for the given path
	 * @param string $path Path to map to an app
	 * @return bool True if an app exists, false otherwise.
	 */
	private function checkApp(string $path): bool{
		if(empty($this->_allowedApplications)) return false;
		if(isset($this->_allowedApplications["*"])) return true;
		return isset($this->_allowedApplications[$path]);
	}

	/**
	 * Recieve client data
	 */
	public function recieve(): void {
		$this->throwIfClosed("receive data");
		//if the client exceed the max request rate, connection is closed.
		if(!$this->counter(true)){
			$this->close(
				IWebsocketProtocol::STATUS_MESSAGE_VIOLATES_SERVER_POLICY,
				"Max request by second rate reached."
			);
			return;
		}
		if($this->_handshaked) $this->handle();
		else $this->handshake();
	}

	private function handshake():void{
		try{
			$data = $this->read();
		}catch(\Error | \Exception $e){
			$this->_dispatcher->dispatch(new ErrorOcurred($this->_id,$e));
			return;
		}

		if(isset($this->_rejectIps[$this->_ip])){
			$this->sendHttpResponse(429);
			return;
		}

		if(!is_null($this->_rejectOnHandshakeCode)){
			$this->sendHttpResponse(
				$this->_rejectOnHandshakeCode,
				"$this->_rejectOnHandshakeCode Connection rejected by server ($this->_rejectOnHandshakeMessage)"
			);
			return;
		}
		$lines = preg_split("/\r\n/", $data);

		// check for valid http-header:
		if (!preg_match('/\AGET (\S+) HTTP\/1.1\z/', $lines[0], $matches)) {
			$this->sendHttpResponse(400);
			return;
		}

		// check for valid application:
		$path = $matches[1];
		$appKey = strlen($path) > 1 ? substr($path,1) : '';

		if(!$this->checkApp($appKey)){
			$this->sendHttpResponse(404, '404 application not found');
			return;
		}
		$this->_app = empty($appKey) ? '*' : $appKey;

		// generate headers array:
		$headers = [];
		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}

		$this->_headers = $headers;

		// check for supported websocket version:
		if (!isset($headers['Sec-WebSocket-Version']) || $headers['Sec-WebSocket-Version'] < 6) {
			$this->sendHttpResponse(501);
			return;
		}

		// check origin:
		if (!empty($this->_allowedOrigins)) {
			$origin = (isset($headers['Sec-WebSocket-Origin'])) ? $headers['Sec-WebSocket-Origin'] : '';
			$origin = (isset($headers['Origin'])) ? $headers['Origin'] : $origin;
			if (empty($origin) || !$this->checkOrigin($origin)) {
				$this->sendHttpResponse(401);
				return;
			}
		}

		// do handyshake: (hybi-10)
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$response = "HTTP/1.1 101 Switching Protocols\r\n";
		$response .= "Upgrade: websocket\r\n";
		$response .= "Connection: Upgrade\r\n";
		$response .= "Sec-WebSocket-Accept: " . $secAccept . "\r\n";
		if (isset($headers['Sec-WebSocket-Protocol']) && !empty($headers['Sec-WebSocket-Protocol'])) {
			$response .= "Sec-WebSocket-Protocol: " . substr($path, 1) . "\r\n";
		}
		$response .= "\r\n";
		try {
			$this->write($response);
		} catch (\Error | \Exception $e) {
			$this->_dispatcher->dispatch(new ErrorOcurred($this->_id,$e));
			return;
		}
		$this->_handshaked = true;
		$this->_dispatcher->dispatch(new Handshaked(
			new WebsocketSender($this), $this
		));
		if(!empty($this->_queue)) foreach($this->_queue as $message) $this->send(...$message);
	}

	/**
	 * Sends an http response to client.
	 *
	 * @param int         $httpStatusCode
	 * @param null|string $message
	 */
	private function sendHttpResponse(int $httpStatusCode = 400, ?string $message = null): void {
		$httpHeader = 'HTTP/1.1 ';
		switch ($httpStatusCode) {
			case 400:
				$httpHeader .= $message ?? '400 Bad Request';
				break;
			case 401:
				$httpHeader .= $message ?? '401 Unauthorized';
				break;
			case 403:
				$httpHeader .= $message ?? '403 Forbidden';
				break;
			case 404:
				$httpHeader .= $message ?? '404 Not Found';
				break;
			case 413 :
				$httpHeader .= $message ?? '413 Entity too large';
				break;
			case 429 :
				$httpHeader .= $message ?? '429 Too many requests';
				break;
			case 501:
				$httpHeader .= $message ?? '501 Not Implemented';
				break;
		}
		$httpHeader .= "\r\n";
		try {
			$this->write($httpHeader);
			stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
			$this->_closed = true;
			$this->_dispatcher->dispatch(new ErrorOcurred(
				$this->_id,
				new WebsocketHandshakeFailure($httpStatusCode, $httpHeader)
			));
		} catch (\Error | \Exception $e) {
			$this->_dispatcher->dispatch(new ErrorOcurred($this->_id,$e));
		}
	}

	private function handle():void{
		try{
			$data = $this->read();
		}catch(\Error | \Exception $e){
			$this->_dispatcher->dispatch(new ErrorOcurred($this->_id,$e));
			return;
		}

		if ($this->_waitingForData) {
			$data = $this->_dataBuffer . $data;
			$this->_dataBuffer = '';
			$this->_waitingForData = false;
		}

		$decodedData = $this->_protocol->decode($data);

		if (empty($decodedData)) {
			$this->_waitingForData = true;
			$this->_dataBuffer .= $data;
			return;
		} else {
			$this->_dataBuffer = '';
			$this->_waitingForData = false;
		}

		if (!isset($decodedData['type'])) {
			$this->sendHttpResponse(401);
			return;
		}

		switch ($decodedData['type']) {
			case IWebsocketProtocol::TEXT:
				if($this->counter()) $this->_dispatcher->dispatch(new DataRecieved(
					$this->_id,$decodedData['payload']
				));
				break;
			case IWebsocketProtocol::BINARY:
				$this->close(1003);
				break;
			case IWebsocketProtocol::PING:
				$this->send($decodedData['payload'], IWebsocketProtocol::PONG, false);
				break;
			case IWebsocketProtocol::PONG:
				// server currently not sending pings, so no pong should be received.
				break;
			case IWebsocketProtocol::CLOSE:
				$this->close();
				break;
		}
	}

	/**
	 * @param bool $checkOnly If true, will only perform the check without counting.
	 * @return bool True if the request can be handle, false otherwise, depending on the maxRequestBySecond
	 *              rate.
	 */
	private function counter(bool $checkOnly = false):bool{
		if($this->_maxRequestByMinute <= 0) return true;
		if(!$checkOnly) $this->_requestsCounter++;
		if(time() - $this->_requestsCounterDate > 60){
			if(!$checkOnly){
				$this->_requestsCounterDate = time();
				$this->_requestsCounter = 0;
			}
		}else if($this->_requestsCounter > $this->_maxRequestByMinute) return false;
		return true;
	}

	/**
	 * @param string $op Action
	 * @throws WebsocketConnectionClosed
	 */
	private function throwIfClosed(string $op):void{
		if($this->_unserialized) throw new InvalidWebsocketConnection(
			"Attempting to perform the following action on an unserialized connection : $op ($this->_id)."
		);
		if($this->_closed) throw new WebsocketConnectionClosed(
			"Attempting to perform the following action on a closed connection : $op ($this->_id)."
		);
	}

	/**
	 * @return string Data read from socket
	 * @throws WebsocketConnectionClosed
	 * @throws WebsocketIOFailure
	 */
	private function read():string{
		$buffer = '';
		$buffsize = 8192;
		$metadata['unread_bytes'] = 0;
		$maxSize = $this->_handshaked ? $this->_maxReadBufferSize : $this->_maxRequestHandshakeSize;
		do {
			if (feof($this->_socket)) throw new WebsocketIOFailure(
				"No more data to read from client socket $this->_id. Incomplete message recieved."
			);
			$result = fread($this->_socket, $buffsize);
			if ($result === false || feof($this->_socket)) throw new WebsocketIOFailure(
				"Could not read more data from socket $this->_id"
			);
			$buffer .= $result;
			$metadata = stream_get_meta_data($this->_socket);
			$buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
			if($buffsize > $maxSize){
				if($this->_handshaked) $this->close(IWebsocketProtocol::STATUS_MESSAGE_TOO_LARGE);
				else $this->sendHttpResponse(413);
				throw new WebsocketIOFailure(
					($this->_handshaked ? "Message" : "Header")
					." too large from $this->_id ($maxSize limit exceed). Client connection closed."
				);
			}
		} while ($metadata['unread_bytes'] > 0);

		return $buffer;
	}

	/**
	 * Send data to client
	 *
	 * @param string $payload
	 * @param string $type
	 * @param bool   $masked
	 */
	public function send(string $payload, string $type = IWebsocketProtocol::TEXT, bool $masked = true): void {
		$this->throwIfClosed("send data");
		if($this->_handshaked){
			try{
				$this->write($this->_protocol->encode($payload, $type, $masked));
				$this->_dispatcher->dispatch(new DataSent($this->_id,$payload));
			}catch(\Error | \Exception $e){
				$this->_dispatcher->dispatch(new ErrorOcurred($this->_id,$e));
			}
		}else $this->_queue[] = [$payload, $type, $masked];
	}

	/**
	 * @param string $data data to write in client socket
	 * @return int
	 */
	private function write(string $data):int{
		$stringLength = strlen($data);
		if ($stringLength === 0) return 0;
		else if($stringLength > $this->_maxWriteBufferSize && $this->_maxReadBufferSize > 0)
			throw new WebsocketIOFailure("Max response size reached ($this->_maxWriteBufferSize)");

		for ($written = 0; $written < $stringLength; $written += $fwrite) {
			$fwrite = @fwrite($this->_socket, substr($data, $written));
			if ($fwrite === false) throw new WebsocketIOFailure(
				"Unexpected error while trying to write in stream $this->_id."
			);
			if ($fwrite === 0) throw new WebsocketIOFailure(
				"Unable to write in stream $this->_id."
			);
		}

		return $written;
	}

	/**
	 * Close the connection
	 *
	 * @param int         $statusCode
	 * @param null|string $msg
	 * @throws WebsocketConnectionClosed
	 */
	public function close(
		int $statusCode = IWebsocketProtocol::STATUS_NORMAL_CLOSE,
		?string $msg = null
	): void {
		$this->throwIfClosed("close connection");
		$payload = str_split(sprintf('%016b', $statusCode), 8);
		$payload[0] = chr(bindec($payload[0]));
		$payload[1] = chr(bindec($payload[1]));
		$payload = implode('', $payload);

		switch ($statusCode) {
			case IWebsocketProtocol::STATUS_NORMAL_CLOSE:
				$payload .= $message = 'normal closure';
				break;
			case IWebsocketProtocol::STATUS_GOING_AWAY:
				$payload .= $message = 'going away';
				break;
			case IWebsocketProtocol::STATUS_PROTOCOL_ERROR:
				$payload .= $message = 'protocol error';
				break;
			case IWebsocketProtocol::STATUS_UNKNOWN_DATA:
				$payload .= $message = 'unknown data';
				break;
			case IWebsocketProtocol::STATUS_MESSAGE_TOO_LARGE:
				$payload .= $message = 'frame too large';
				break;
			case IWebsocketProtocol::STATUS_INCONSISTENT_DATA:
				$payload .= $message = 'utf8 expected';
				break;
			case IWebsocketProtocol::STATUS_MESSAGE_VIOLATES_SERVER_POLICY:
				$payload .= $message = 'message violates server policy';
				break;
			default :
				$payload .= $message = 'Unknown error';
		}
		$this->send($payload, 'close', false);
		stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
		$this->_closed = true;

		$this->_dispatcher->dispatch(new Closed(
			$this,$statusCode,$msg ?? $message
		));
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

	/**
	 * @return resource|null
	 */
	public function getSocket() {
		return $this->_socket;
	}

	/**
	 * @return bool True if connection is closed, false otherwise
	 */
	public function isClosed(): bool {
		return $this->_closed;
	}

	public function __destruct() {
		$this->close();
	}

	/**
	 * @return array|null Headers used for handshake
	 */
	public function getHeaders(): ?array {
		return $this->_headers;
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return [
			"app" => $this->getApp(),
			"id" => $this->getId(),
			"ip" => $this->getIp(),
			"port" => $this->getPort(),
			"headers" => $this->getHeaders()
		];
	}

	/**
	 * @return string App linked to the current connection
	 */
	public function getApp(): string {
		return $this->_app;
	}

	/**
	 * String representation of object
	 *
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize([
			$this->_id,
			$this->_app,
			$this->_headers,
			$this->_allowedOrigins,
			$this->_port
		]);
	}

	/**
	 * Constructs the object
	 *
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) {
		$this->_unserialized = true;
		list(
			$this->_id,
			$this->_app,
			$this->_headers,
			$this->_allowedOrigins,
			$this->_port
		) = unserialize($serialized);
	}
}