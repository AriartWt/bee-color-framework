<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/06/18
 * Time: 16:36
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\DisableUsers;

/**
 * Désactive une liste d'utilisateur
 */
final class DisableUsersHandler extends UserCommandHandler{
	
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var DisableUsers $command */
		$users = $this->getAll(...$command->getUsers());
		foreach($users as $user){
			$user->disable($command->getDisabler());
		}
		$this->repos()->modifyAll($command,...$users);
	}
}