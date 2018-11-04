<?php
namespace wfw\engine\core\action;

/**
 * Permet de router une action vers son handler
 */
interface IActionRouter {
	/**
	 * @param IAction $action Action à router
	 * @return IActionHandler Handler destinataire de l'action
	 */
	public function findActionHandler(IAction $action):IActionHandler;
}