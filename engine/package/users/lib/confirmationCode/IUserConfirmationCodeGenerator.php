<?php
namespace wfw\engine\package\users\lib\confirmationCode;

/**
 *  Generateur de code de confirmation d'utilisateur
 */
interface IUserConfirmationCodeGenerator {
	/**
	 * @return UserConfirmationCode
	 */
	public function createUserConfirmationCode():UserConfirmationCode;
}