<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/06/18
 * Time: 17:03
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\RemoveUsers;

/**
 * Supprime une liste d'utilisateurs
 */
final class RemoveUsersHandler extends UserCommandHandler{
	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var RemoveUsers $command */
		$users = $this->getAll(...$command->getUsers());
		foreach ($users as $user){
			$user->remove($command->getRemoverId());
		}
		$this->repos()->modifyAll($command,...$users);
	}
}