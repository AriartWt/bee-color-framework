<?php
namespace wfw\engine\package\general\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;

/**
 * Sert à maintenir une session PHP en vie aussi longtemps qu'une interface en a besoin.
 */
final class HeartBeatHandler implements IActionHandler {
	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()) return new Response();
		return new ErrorResponse("404","Not found");
	}
}