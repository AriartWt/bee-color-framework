<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:48
 */

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
	public function handle(ICommand $command) {
		/** @var ConfirmUserMailChange $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->confirmEmail(
			$command->getCode(),
			$command->getConfirmer(),
			$command->getState()
		);
		$this->repos()->modify($user,$command);
	}
}