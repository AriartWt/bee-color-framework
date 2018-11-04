<?php
namespace wfw\engine\core\command;

/**
 *  Permet de traiter une commande
 */
interface ICommandHandler {
	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command);
}