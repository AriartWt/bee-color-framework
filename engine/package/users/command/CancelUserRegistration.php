<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/06/18
 * Time: 16:44
 */

namespace wfw\engine\package\users\command;

/**
 * Commande d'annulation d'enregistrement d'un utilisateur
 */
final class CancelUserRegistration extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var string $_modifierId */
	private $_modifierId;
	/** @var bool $_removeUser */
	private $_removeUser;

	/**
	 * CancelPasswordRetrieving constructor.
	 * @param string $userId
	 * @param string $modifierId
	 * @param bool $removeUser
	 */
	public function __construct(string $userId, string $modifierId, bool $removeUser = false) {
		parent::__construct();
		$this->_modifierId = $modifierId;
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
	 * @return string
	 */
	public function getModifierId(): string {
		return $this->_modifierId;
	}

	/**
	 * @return bool
	 */
	public function removeUser(): bool {
		return $this->_removeUser;
	}
}