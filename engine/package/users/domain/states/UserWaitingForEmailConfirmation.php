<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 01:06
 */

namespace wfw\engine\package\users\domain\states;

use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  En attente de confirmation de l'adresse email
 */
final class UserWaitingForEmailConfirmation extends UserState {
	/** @var UserConfirmationCode $_code */
	private $_code;
	/** @var Email $_email */
	private $_email;

	/**
	 *  UserWaitingForEmailConfirmation constructor.
	 *
	 * @param Email                $email Adresse mail testée
	 * @param UserConfirmationCode $code  Code de confirmation
	 */
	public function __construct(Email $email,UserConfirmationCode $code) {
		$this->_code = $code;
		$this->_email = $email;
	}

	/**
	 *  Vérifie la validité d'un code de confirmation
	 *
	 * @param UserConfirmationCode $code Code à tester
	 *
	 * @return bool
	 */
	public function isValide(UserConfirmationCode $code):bool{
		return $this->_code->equals($code);
	}

	/**
	 * @return Email
	 */
	public function getEmail():Email{
		return $this->_email;
	}
}