<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 02:59
 */

namespace wfw\engine\package\users\domain;

/**
 *  ValueObject mot de passe
 */
class Password
{
	/** @var bool|string $_password */
	private $_password;

	/**
	 *  Password constructor.
	 *
	 * @param string $password Mot de passe
	 */
	public function __construct(string $password) {
		$length = strlen($password);//fix this...
		if($length < 8 || $length > 128) throw new \InvalidArgumentException(
			"Password length have to be between 8 and 128 characters !"
		);
		$this->_password = password_hash($password,PASSWORD_BCRYPT);
	}

	/**
	 * Teste l'égalité d'un mot de passe avec le mot de passe courant
	 * @param string $password Mot de passe à tester
	 * @return bool
	 */
	public function equals(string $password){
		return password_verify($password,$this->_password);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_password;
	}
}