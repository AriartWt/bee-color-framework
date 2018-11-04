<?php
namespace wfw\engine\package\users\domain\states;

use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  En attente de réinitialisation de mot de passe
 */
final class UserWaitingForPasswordReset extends UserState {
	/** @var UserConfirmationCode $_code */
	private $_code;

	/**
	 *  UserWaitingForPasswordReset constructor.
	 *
	 * @param UserConfirmationCode $code Code de confirmation
	 */
	public function __construct(UserConfirmationCode $code)
	{
		$this->_code = $code;
	}

	/**
	 * @return UserConfirmationCode
	 */
	public function getCode():UserConfirmationCode{
		return $this->_code;
	}

	/**
	 *  Vérifie la validité d'un code de confirmation
	 *
	 * @param UserConfirmationCode $code Code à vérifier
	 *
	 * @return bool
	 */
	public function isValide(UserConfirmationCode $code):bool{
		return $this->_code->equals($code);
	}
}