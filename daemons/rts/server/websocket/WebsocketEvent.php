<?php

namespace wfw\daemons\rts\server\websocket;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Evenement websocket
 * Evenement de base :
 *      connected : une socket est connectée (handhaske Ok),
 *      closed : une socket est fermée,
 *      accepted : une connection a été acceptée (no handshake yet),
 *      rejected : une connection a été rejectée (handshake Ko),
 *      msg_recieved : un message a été reçu,
 *      msg_sent : un message a été envoyé
 */
abstract class WebsocketEvent implements IWebsocketEvent{
	/** @var string $_id */
	private $_id;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var bool $_propagationStopped */
	private $_propagationStopped;

	/**
	 * WebsocketEvent constructor.
	 */
	public function __construct() {
		$this->_id = (string) new UUID(UUID::V6);
		$this->_creationDate = microtime(true);
		$this->_propagationStopped = false;
	}

	/**
	 * @return string Event Id
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return float event creation date in microseconds
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * The event propagation MUST be stopped.
	 */
	public function stopPropagation(): void {
		$this->_propagationStopped = true;
	}

	/**
	 * @return bool True if the event propagation is stopped
	 */
	public function isPropagationStopped(): bool {
		return $this->_propagationStopped;
	}
}