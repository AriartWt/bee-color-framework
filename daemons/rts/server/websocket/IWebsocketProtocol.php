<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Protocole permettant de gérer les écritures et les lectures dans des websockets.
 *
 * Le protocole va envoyer des événements pour chaque connexion, déconnexion, message reçu etc.
 */
interface IWebsocketProtocol{
	public const TEXT = "text";
	public const BINARY = "binary";
	public const PING = "ping";
	public const PONG = "pong";
	public const CLOSE = "close";

	public const STATUS_NORMAL_CLOSE = 1000;
	public const STATUS_GOING_AWAY = 1001;
	public const STATUS_PROTOCOL_ERROR = 1002;
	public const STATUS_UNKNOWN_DATA = 1003;
	//according to RFC, the usage of this code is not yet defined, but is reserved
	public const STATUS_RESERVED = 1004;
	public const STATUS_NO_STATUS_CODE_FOUND = 1005;
	//abnormal closing (when no Clode frame was sent/recieved)
	public const STATUS_ABNORMAL_CLOSING = 1006;
	//e.g. non utf-8 chars
	public const STATUS_INCONSISTENT_DATA = 1007;
	public const STATUS_MESSAGE_VIOLATES_SERVER_POLICY = 1008;
	public const STATUS_MESSAGE_TOO_LARGE = 1009;
	public const STATUS_SERVER_DOESNT_IMPLEMENTS_REQUIRED_EXTENSIONS = 1010;
	public const STATUS_UNABLE_TO_FULFILL_REQUEST = 1011;
	public const STATUS_TLS_HANDSHAKE_FAILURE = 1015;

	/**
	 * Encode the payload.
	 * @param string $payload Payload to encode
	 * @param string $type Encoding type
	 * @param bool   $masked
	 * @return string
	 */
	public function encode(string $payload, string $type = self::TEXT, bool $masked = true):string;

	/**
	 * @param string $data Data to decode
	 * @return array with two keys : payload and type
	 */
	public function decode(string $data):array;
}