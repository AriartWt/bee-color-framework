<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:24
 */

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
	public function handle(ICommand $command) {
		/** @var ConfirmUserRegistration $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->confirm(
			$command->getCode(),
			(strlen($command->getConfirmer())>0)
				? $command->getConfirmer()
				: $command->getUserId(),
			$command->getState()
		);
		$this->repos()->modify($user,$command);
	}
}