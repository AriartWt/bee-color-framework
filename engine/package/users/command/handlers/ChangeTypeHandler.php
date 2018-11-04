<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ChangeType;
use wfw\engine\package\users\domain\User;

/**
 * Applique la commande ChangeType
 */
final class ChangeTypeHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var ChangeType $command */
		/** @var User $user */
		$user = $this->get($command->getUserId());
		$user->changeType($command->getType(),$command->getModifierId());
		$this->repos()->modify($user,$command);
	}
}