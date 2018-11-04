<?php
namespace wfw\engine\core\action;
use wfw\engine\core\response\IResponse;

/**
 * Handler d'Action
 */
interface IActionHandler {
	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action):IResponse;
}