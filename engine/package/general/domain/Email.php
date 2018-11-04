<?php
namespace wfw\engine\package\general\domain;

/**
 *  Représente un email valide.
 */
class Email {
	/** @var string $_email */
	private $_email;

	/**
	 *  L'email est vérifié grâce à la fonction filter_var flag 274 (FILTER_VALIDATE_EMAIL)
	 * @param string $email Email
	 */
	public function __construct(string $email) {
		if(filter_var($email,FILTER_VALIDATE_EMAIL)){
			$this->_email = $email;
		}else{
			throw new \InvalidArgumentException("$email is not a valid email adresse !");
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_email;
	}
}