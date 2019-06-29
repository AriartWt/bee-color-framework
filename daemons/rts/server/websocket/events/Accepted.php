<?php

namespace wfw\daemons\rts\server\websocket\events;

use wfw\daemons\rts\server\websocket\WebsocketEvent;

/**
 * When a new client is accepted
 */
final class Accepted extends WebsocketEvent {
	/** @var string $_ip */
	private $_ip;
	/** @var string $_port */
	private $_port;
	/** @var array $_infos */
	private $_infos;

	/**
	 * Accepted constructor.
	 *
	 * @param string $socketId
	 * @param string $clientIp
	 * @param string $clientPort
	 * @param array  $infos
	 */
	public function __construct(string $socketId, string $clientIp, string $clientPort, array $infos = []) {
		parent::__construct($socketId);
		$this->_ip = $clientIp;
		$this->_port = $clientPort;
		$this->_infos = $infos;
	}

	/**
	 * @return string
	 */
	public function getIp(): string {
		return $this->_ip;
	}

	/**
	 * @return string
	 */
	public function getPort(): string {
		return $this->_port;
	}

	/**
	 * @return array
	 */
	public function getInfos(): array {
		return $this->_infos;
	}
}