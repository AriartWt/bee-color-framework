<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 06:18
 */

namespace wfw\engine\package\users\lib\confirmationCode;

/**
 *  Generateur de code de confirmation d'utilisateur
 */
interface IUserConfirmationCodeGenerator
{
	/**
	 * @return UserConfirmationCode
	 */
	public function createUserConfirmationCode():UserConfirmationCode;
}