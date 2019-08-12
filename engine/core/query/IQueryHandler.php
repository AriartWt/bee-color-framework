<?php
namespace wfw\engine\core\query;

/**
 *  Permet de traiter une commande
 */
interface IQueryHandler {
	/**
	 * Traite la commande
	 * @param IQuery $command Commande à traiter
	 */
	public function handleCommand(IQuery $command);
}