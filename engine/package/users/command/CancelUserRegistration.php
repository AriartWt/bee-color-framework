<?php
namespace wfw\engine\package\users\command;

/**
 * Commande d'annulation d'enregistrement d'un utilisateur
 */
final class CancelUserRegistration extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var bool $_removeUser */
	private $_removeUser;

	/**
	 * CancelPasswordRetrieving constructor.
	 * @param string $userId
	 * @param string $modifierId
	 * @param bool $removeUser
	 */
	public function __construct(string $userId, string $modifierId, bool $removeUser = false) {
		parent::__construct($modifierId);
		$this->_removeUser = $removeUser;
		$this->_userId = $userId;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return bool
	 */
	public function removeUser(): bool {
		return $this->_removeUser;
	}
}