<?php
namespace wfw\engine\package\users\command;

/**
 * Annule une procédure de récupération de mot de passe.
 */
final class CancelPasswordRetrieving extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var string $_modifierId */
	private $_modifierId;

	/**
	 * CancelPasswordRetrieving constructor.
	 * @param string $userId
	 * @param string $modifierId
	 */
	public function __construct(string $userId, string $modifierId) {
		parent::__construct();
		$this->_modifierId = $modifierId;
		$this->_userId = $userId;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return string
	 */
	public function getModifierId(): string {
		return $this->_modifierId;
	}
}