<?php
namespace wfw\engine\package\users\lib\password;

use wfw\engine\package\users\domain\HashedPassword;
use wfw\engine\package\users\domain\Password;

/**
 *  Permet de hasher un mot de passe
 */
interface IPasswordHasher {
	/**
	 *  Hash un password
	 * @param Password $pwd Password à hasher
	 * @return HashedPassword Password hashé
	 */
	public function hashPassword(Password $pwd):HashedPassword;
}