<?php
namespace wfw\daemons\modelSupervisor\server\responses;

/**
 *  Login autorisé
 */
final class AccessGranted extends AbastractMSServerResponse {
	/** @var string $_sessionId */
	private $_sessionId;

	/**
	 * AccessGranted constructor.
	 *
	 * @param string $sessId Identifiant de session
	 */
	public function __construct(string $sessId) {
		$this->_sessionId = $sessId;
	}

	/**
	 * @return string
	 */
	public function getSessionId(): string {
		return $this->_sessionId;
	}
}