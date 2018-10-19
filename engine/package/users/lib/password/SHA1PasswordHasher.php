<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 03:26
 */

namespace wfw\engine\package\users\lib\password;


use wfw\engine\package\users\domain\HashedPassword;
use wfw\engine\package\users\domain\Password;

/**
 *  Permet de hasher un mot de passe grace à l'algorythme sha1
 */
final class SHA1PasswordHasher implements IPasswordHasher
{
	/**
	 *  Hash un password
	 *
	 * @param Password $pwd Password à hasher
	 *
	 * @return HashedPassword Password hashé
	 */
	public function hashPassword(Password $pwd): HashedPassword {
		return new HashedPassword(sha1((string)$pwd));
	}
}