<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ConfirmUserMailChange;
use wfw\engine\package\users\domain\User;

/**
 * Gere la commande de confirmation de changement d'adresse mail d'un utilisateur
 */
final class ConfirmUserMailChangeHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var ConfirmUserMailChange $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->confirmEmail(
			$command->getCode(),
			$command->getInitiatorId(),
			$command->getState()
		);
		$this->repos()->modify($user,$command);
	}
}