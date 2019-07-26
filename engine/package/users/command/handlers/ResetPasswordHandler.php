<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ResetPassword;

/**
 * Gére la commande de reset de mot de passe
 */
final class ResetPasswordHandler extends UserCommandHandler {
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var ResetPassword $command */
		$user = $this->get($command->getUserId());
		$user->resetPassword(
			$command->getPassword(),
			$command->getCode(),
			$command->getAskerId()
		);
		$this->repos()->modify($user,$command);
	}
}