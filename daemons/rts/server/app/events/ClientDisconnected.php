<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\websocket\IWebsocketConnection;

/**
 * When a client connection have been closed
 */
final class ClientDisconnected extends RTSAppEvent {
	/** @var IWebsocketConnection $_connection */
	private $_connection;
	/** @var int $_code */
	private $_code;
	/** @var float $_date */
	private $_date;
	/** @var string $_message */
	private $_message;

	/**
	 * ClientConnected constructor.
	 *
	 * @param IWebsocketConnection $connection
	 * @param float                $date
	 * @param string               $message
	 * @param int                  $code
	 */
	public function __construct(IWebsocketConnection $connection, float $date, string $message, int $code) {
		parent::__construct(
			'',
			null,
			IRTSAppEvent::SCOPE | IRTSAppEvent::CENTRALIZATION | IRTSAppEvent::DISTRIBUTION,
			null
		);
		$this->_connection = $connection;
		$this->_date = $date;
		$this->_message = $message;
		$this->_code = $code;
	}

	/**
	 * @return IWebsocketConnection
	 */
	public function getConnection(): IWebsocketConnection {
		return $this->_connection;
	}

	/**
	 * @return int
	 */
	public function getCode(): int {
		return $this->_code;
	}

	/**
	 * @return float
	 */
	public function getDate(): float {
		return $this->_date;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->_message;
	}
}