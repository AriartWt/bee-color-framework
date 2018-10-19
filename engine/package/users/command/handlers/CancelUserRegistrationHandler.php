<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/06/18
 * Time: 16:53
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\CancelUserRegistration;

/**
 * Applique la commande d'annulation d'enregistrement d'un utilisateur
 */
final class CancelUserRegistrationHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var CancelUserRegistration $command */
		$user = $this->get($command->getUserId());
		$user->cancelRegistration($command->getModifierId(),$command->removeUser());
		$this->repos()->modify($user,$command);
	}
}