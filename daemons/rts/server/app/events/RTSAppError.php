<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\daemons\rts\server\websocket\IWebsocketConnection;

/**
 * Used to dispatch an error to all apps ()
 */
final class RTSAppError extends RTSAppEvent {
	/** @var IWebsocketConnection $_connection */
	private $_connection;
	/** @var \Throwable $_error */
	private $_error;

	/**
	 * RTSAppError constructor.
	 *
	 * @param IWebsocketConnection $connection
	 * @param \Throwable           $error
	 */
	public function __construct(IWebsocketConnection $connection, \Throwable $error) {
		parent::__construct(
			'',
			null,
			IRTSAppEvent::SCOPE,
			[$connection->getApp()]
		);
		$this->_connection = $connection;
		$this->_error = $error;
	}

	/**
	 * @return IWebsocketConnection
	 */
	public function getConnection(): IWebsocketConnection {
		return $this->_connection;
	}

	/**
	 * @return \Throwable
	 */
	public function getError(): \Throwable {
		return $this->_error;
	}
}