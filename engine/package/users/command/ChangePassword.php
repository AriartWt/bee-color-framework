<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:51
 */

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
	/** @var string $_modifier */
	private $_modifier;

	/**
	 * ChangePassword constructor.
	 * @param string $userId
	 * @param Password $old
	 * @param Password $new
	 * @param string $modifier
	 */
	public function __construct(string $userId, Password $old, Password $new, string $modifier) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_old = $old;
		$this->_new = $new;
		$this->_modifier = $modifier;
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

	/**
	 * @return string
	 */
	public function getModifier(): string {
		return $this->_modifier;
	}
}