<?php
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
	public function handleCommand(ICommand $command) {
		/** @var CancelUserRegistration $command */
		$user = $this->get($command->getUserId());
		$user->cancelRegistration($command->getModifierId(),$command->removeUser());
		$this->repos()->modify($user,$command);
	}
}