<?php
namespace wfw\engine\package\users\command;


use wfw\engine\package\users\domain\Password;

/**
 * Commande de changement de mot de passe
 */
final class ChangePassword extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var Password $_old */
	private $_old;
	/** @var Password $_new */
	private $_new;

	/**
	 * ChangePassword constructor.
	 * @param string $userId
	 * @param Password $old
	 * @param Password $new
	 * @param string $modifier
	 */
	public function __construct(string $userId, Password $old, Password $new, string $modifier) {
		parent::__construct($modifier);
		$this->_userId = $userId;
		$this->_old = $old;
		$this->_new = $new;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return Password
	 */
	public function getOld(): Password {
		return $this->_old;
	}

	/**
	 * @return Password
	 */
	public function getNew(): Password {
		return $this->_new;
	}
}