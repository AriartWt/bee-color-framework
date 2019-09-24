<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ConfirmUserRegistration;
use wfw\engine\package\users\domain\User;

/**
 * Gère la commande de confirmation d'inscription d'un utilisateur
 */
final class ConfirmUserRegistrationHandler extends UserCommandHandler{

	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var ConfirmUserRegistration $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->confirm(
			$command->getCode(),
			(strlen($command->getInitiatorId())>0)
				? $command->getInitiatorId()
				: $command->getUserId(),
			$command->getState()
		);
		$this->repos()->modify($user,$command);
	}
}