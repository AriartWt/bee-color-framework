<?php
namespace wfw\engine\package\users\lib\confirmationCode;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Crée un identifiant unique utilisé pour créer un code de confirmation d'utilisateur
 */
final class UUIDBasedUserConfirmationCodeGenerator implements IUserConfirmationCodeGenerator {
	/**
	 * @return UserConfirmationCode
	 */
	public function createUserConfirmationCode(): UserConfirmationCode {
		return new UserConfirmationCode((new UUID())->get());
	}
}