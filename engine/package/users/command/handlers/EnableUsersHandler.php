<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\EnableUsers;

/**
 * handler de la commande EnableUsers
 */
final class EnableUsersHandler extends UserCommandHandler{
	
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var EnableUsers $command */
		$users = $this->getAll(...$command->getUsers());
		foreach($users as $user){
			$user->enable($command->getEnabler());
		}
		$this->repos()->modifyAll($command,...$users);
	}
}