<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Base rts class
 */
class RTSApp implements IRTSApp{
	/** @var array $_listeners */
	private $_listeners;
	/** @var string $_id */
	private $_id;

	/**
	 * RTSApp constructor.
	 */
	public function __construct() {
		$this->_listeners = [];
		$this->_id = (string) new UUID(UUID::V4);
	}

	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 *
	 * @return string The app key
	 */
	public function getKey(): string {
		return '*';
	}

	/**
	 * @return string
	 */
	public final function getId(): string {
		return $this->_id;
	}

	/**
	 * @param string $data
	 * @return IRTSAppEvent[]
	 */
	public function receiveData(string $data): array {
		// TODO: Implement receiveData() method.
	}

	/**
	 * @param IRTSAppEvent[] $event
	 */
	public function applyRTSEvents(IRTSAppEvent ...$event) {
		// TODO: Implement applyRTSEvents() method.
	}
}