<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\errors\NoQueryHandlerFound;

/**
 * Dispatcher de commandes
 */
interface IQueryDispatcher {
	/**
	 * @param IQuery $command Commande à dispatcher
	 * @throws NoQueryHandlerFound
	 */
	public function dispatchCommand(IQuery $command):void;
}