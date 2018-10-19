<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:59
 */

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
	public function handle(ICommand $command) {
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