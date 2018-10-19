<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 06:11
 */

namespace wfw\engine\package\users\domain\states;

use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  Un utilisateur dans cet état ne peut pas utiliser les privilége d'un utilisateur actif car son compte n'est pas confirmé
 */
final class UserWaitingForRegisteringConfirmation extends UserState {
	/** @var UserConfirmationCode $_code */
	private $_code;

	/**
	 *  ToBeConfirmedUser constructor.
	 *
	 * @param UserConfirmationCode $code Code de confirmation
	 */
	public function __construct(UserConfirmationCode $code) {
		$this->_code = $code;
	}

	/**
	 *  Vérifie que le code fourni est bien le code attendu
	 *
	 * @param UserConfirmationCode $code Code à vérifier
	 *
	 * @return bool
	 */
	public function isValide(UserConfirmationCode $code):bool{
		return $this->_code->equals($code);
	}

	/**
	 * @return UserConfirmationCode
	 */
	public function getCode():UserConfirmationCode{
		return $this->_code;
	}
}