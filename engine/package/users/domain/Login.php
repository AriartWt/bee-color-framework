<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 02:58
 */

namespace wfw\engine\package\users\domain;

/**
 * Login d'un utilisateur
 */
class Login
{
	/** @var string $_login */
	private $_login;
	
	/**
	 * Login constructor.
	 *
	 * @param string $login Login
	 */
	public function __construct(string $login) {
		$length = strlen($login);
		if($length < 4 || $length > 128) throw new \InvalidArgumentException(
			"The login length have to be between 4 and 128 characters !"
		);
		$this->_login = $login;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_login;
	}
}