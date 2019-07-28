<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ChangePassword;
use wfw\engine\package\users\domain\User;

/**
 * Gére la commande de changement de mot de passe.
 */
final class ChangePasswordHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var ChangePassword $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->changePassword(
			$command->getOld(),
			$command->getNew(),
			$command->getInitiatorId()
		);
		$this->repos()->modify($user,$command);
	}
}