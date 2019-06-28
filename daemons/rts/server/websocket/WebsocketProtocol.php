<?php

namespace wfw\daemons\rts\server\websocket;

use wfw\daemons\rts\server\websocket\errors\WebsocketProtocolFailure;

/**
 * Websocket protocol (hybi10) according to websocket standards
 * @see https://tools.ietf.org/rfc6455
 */
final class WebsocketProtocol implements IWebsocketProtocol {
	/**
	 * Encode the payload.
	 *
	 * @param string $payload Payload to encode
	 * @param string $type    Encoding type
	 * @param bool   $masked
	 * @return string
	 */
	public function encode(string $payload, string $type = self::TEXT, bool $masked = true): string {
		$frameHead = [];
		$payloadLength = strlen($payload);

		switch ($type) {
			case self::TEXT:
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
				break;

			case self::CLOSE:
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
				break;

			case self::PING:
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
				break;

			case self::PONG:
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
				break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if ($frameHead[2] > 127) {
				throw new WebsocketProtocolFailure(
					self::STATUS_MESSAGE_TOO_LARGE,
					"The payload is too large and can't be encoded."
				);
				//TODO : close connection
				//throw new \RuntimeException('Invalid payload. Could not encode frame.');
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked) {
			// generate a random mask:
			$mask = [];
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}

			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);

		// append payload to frame:
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		return $frame;
	}

	/**
	 * @param string $data Data to decode
	 * @return array with two keys : payload, type. If empty, more data needs to be recieved
	 */
	public function decode(string $data): array {
		$unmaskedPayload = '';
		$decodedData = [];

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// throws if unmasked frame is received:
		if ($isMasked === false) {
			throw new WebsocketProtocolFailure(
				self::STATUS_PROTOCOL_ERROR, "Protocol error (unmasked data received)"
			);
			//TODO : close on protocol error
		}

		switch ($opcode) {
			case 1:
				$decodedData['type'] = self::TEXT;
				break;
			case 2:
				$decodedData['type'] = self::BINARY;
				break;
			case 8:
				$decodedData['type'] = self::CLOSE;
				break;
			case 9:
				$decodedData['type'] = self::PING;
				break;
			case 10:
				$decodedData['type'] = self::PONG;
				break;
			default:
				throw new WebsocketProtocolFailure(
					self::STATUS_UNKNOWN_DATA, "Unknown data"
				);
				//TODO close connection
		}

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return [];
		}

		if ($isMasked === true) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}
}