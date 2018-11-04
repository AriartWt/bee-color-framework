<?php
namespace wfw\engine\package\general\handlers\response;

use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\view\IView;
use wfw\engine\package\general\views\ajax\Ajax;

/**
 * Handler de réponses ajax.
 */
final class AjaxHandler implements IResponseHandler {
	public const DONE_CODE="000";
	public const DATA_CODE="001";

	/** @var null|string $_ajaxViewPath */
	private $_ajaxViewPath;

	/**
	 * AjaxHandler constructor.
	 *
	 * @param null|string $ajaxViewPath Chemin d'accés à la vue utilisée pour le rendu Ajax
	 */
	public function __construct(?string $ajaxViewPath = null) {
		$this->_ajaxViewPath = $ajaxViewPath;
	}

	/**
	 * Renvoie par défaut le code self::DONE_CODE sans données. ou self::DATA_CODE si données
	 * présentes.
	 *
	 * @param IResponse $response Réponse créée par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		$code = self::DONE_CODE;
		$data = null;
		if($response instanceof ErrorResponse){
			$code = $response->getCode();
			$data = $response->getData() ?? $response->getMessage() ?? null;
		}else{
			if(!is_null($response->getData())){
				$code = self::DATA_CODE;
				$data = $response->getData();
			}
		}
		return new Ajax(
			$code,
			(!is_string($data)) ? json_encode($data) : $data,
			$this->_ajaxViewPath
		);
	}
}