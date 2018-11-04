<?php
namespace wfw\engine\package\users\lib\confirmationCode;

/**
 *  Code de confirmation d'utilisateur
 */
final class UserConfirmationCode {
	/** @var string $_code */
	private $_code;

	/**
	 * UserConfirmationCode constructor.
	 *
	 * @param string $code Code de confirmation
	 */
	public function __construct(string $code) {
		$this->_code = $code;
	}

	/**
	 * @param UserConfirmationCode $code
	 *
	 * @return bool
	 */
	public function equals(UserConfirmationCode $code):bool{
		return (string) $this->_code === (string) $code;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_code;
	}
}