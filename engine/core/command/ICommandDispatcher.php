<?php
namespace wfw\engine\core\command;

use wfw\engine\core\command\errors\NoCommandHandlerFound;

/**
 * Dispatcher de commandes
 */
interface ICommandDispatcher {
	/**
	 * @param ICommand $command Commande à dispatcher
	 * @throws NoCommandHandlerFound
	 */
	public function dispatchCommand(ICommand $command):void;
}