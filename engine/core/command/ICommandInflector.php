<?php
namespace wfw\engine\core\command;

use wfw\engine\core\command\errors\NoCommandHandlerFound;

/**
 *  Permet de trouver un handler pour une commande
 */
interface ICommandInflector {
	/**
	 *  Trouve un handler pour une commande
	 *
	 * @param ICommand $command Comande dont on cherche le handler
	 * @return ICommandHandler[]
	 * @throws NoCommandHandlerFound
	 */
	public function resolveHandlers(ICommand $command):array;
}