<?php
namespace wfw\engine\core\response;

use wfw\engine\core\action\IAction;
use wfw\engine\core\response\IResponse;

/**
 * Route une ActionResponse vers un ResponseHandler
 */
interface IResponseRouter {
	/**
	 * @param IAction   $action   Action à l'origine de la réponse.
	 * @param IResponse $response Réponse à router.
	 * @return IResponseHandler Response handler destinataire de $response
	 */
	public function findResponseHandler(
		IAction $action,
		IResponse $response
	):IResponseHandler;
}