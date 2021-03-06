<?php

namespace wfw\daemons\rts\server\app\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Base RTSEvent
 */
abstract class RTSAppEvent implements IRTSAppEvent {
	/** @var string $_id */
	private $_id;
	/** @var string $_data */
	private $_data;
	/** @var array|null $_apps */
	private $_apps;
	/** @var string $_senderId */
	private $_senderId;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var int $_distributionMode */
	private $_distributionMode;

	/**
	 * RTSEvent constructor.
	 *
	 * @param string     $senderId         Client ID which created this event
	 * @param string     $data             Data associated to the event
	 * @param int        $distributionMode Event distribution mode
	 * @param array|null $apps
	 */
	public function __construct(
		string $senderId,
		?string $data = null,
		int $distributionMode = self::CENTRALIZATION,
		?array $apps = ["*"]
	) {
		$this->_apps = $apps;
		$this->_id = (string) new UUID(UUID::V4);
		$this->_creationDate = microtime(true);
		$this->_data = $data;
		$this->_senderId = $senderId;
		$this->_distributionMode = $distributionMode;
	}

	/**
	 * @return string|null Event data
	 */
	public function getData(): ?string {
		return $this->_data;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return float
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * @return string Sender socket Id
	 */
	public function getSenderId(): string {
		return $this->_senderId;
	}

	/**
	 * @param int $mode Mode to test
	 * @return bool True if the mode is enabled, false otherwise
	 */
	public function distributionMode(int $mode): bool {
		return $this->_distributionMode & $mode === $mode;
	}

	/**
	 * @return string[] All apps that can recieve the event. If null, event can be dispatched in
	 *                  every apps.
	 */
	public function getApps(): ?array {
		return $this->_apps;
	}
}