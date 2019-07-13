<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\websocket\IWebsocketConnection;

/**
 * When a new client have been successfully handshaked
 */
final class ClientConnected extends RTSAppEvent {
	/** @var IWebsocketConnection $_connection */
	private $_connection;
	/** @var float $_date */
	private $_date;

	/**
	 * ClientConnected constructor.
	 *
	 * @param IWebsocketConnection $connection
	 * @param float                $date
	 */
	public function __construct(IWebsocketConnection $connection, float $date) {
		parent::__construct(
			'',
			null,
			IRTSAppEvent::SCOPE | IRTSAppEvent::CENTRALIZATION | IRTSAppEvent::DISTRIBUTION,
			[$connection->getApp()]
		);
		$this->_date = $date;
		$this->_connection = $connection;
	}

	/**
	 * @return IWebsocketConnection
	 */
	public function getConnection(): IWebsocketConnection {
		return $this->_connection;
	}

	/**
	 * @return float
	 */
	public function getDate(): float {
		return $this->_date;
	}
}