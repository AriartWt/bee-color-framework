<?php
namespace wfw\engine\core\query;

/**
 *  Reçois les commandes et les redirige vers leur handler
 */
interface IQueryProcessor {
	/**
	 *  Redirige la commande vers son handler
	 *
	 * @param ICommand $command Commande à rediriger
	 *
	 * @return mixed
	 */
	public function executeCommand(ICommand $command):void;
}