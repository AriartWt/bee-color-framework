<?php
namespace wfw\engine\package\users\command\errors;

use wfw\engine\core\command\errors\CommandFailure;

/**L'utilisateur n'a pas été trouvé
 */
final class UserNotFound extends CommandFailure {
	/**
	 * UserNotFound constructor.
	 *
	 * @param string $id Identifiant de l'utilisateur
	 */
	public function __construct(string $id) {
		parent::__construct("User $id not found !");
	}
}