<?php
namespace wfw\engine\package\users\command;

use wfw\engine\package\users\domain\Login;

/**
 * Modifie le Login d'un utilisateur
 */
final class ChangeLogin extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var Login $_login */
	private $_login;
	/** @var string $_modifierId */
	private $_modifierId;

	/**
	 * ChangeLogin constructor.
	 * @param string $userId
	 * @param Login $login
	 * @param string $modifier
	 */
	public function __construct(string $userId,Login $login, string $modifier) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_login = $login;
		$this->_modifierId = $modifier;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return Login
	 */
	public function getLogin(): Login {
		return $this->_login;
	}

	/**
	 * @return string
	 */
	public function getModifierId(): string {
		return $this->_modifierId;
	}
}