<?php
namespace wfw\engine\core\command;

/**
 *  Reçois les commandes et les redirige vers leur handler
 */
interface ICommandBus {
	/**
	 *  Redirige la commande vers son handler
	 *
	 * @param ICommand $command Commande à rediriger
	 *
	 * @return mixed
	 */
	public function executeCommand(ICommand $command):void;
}