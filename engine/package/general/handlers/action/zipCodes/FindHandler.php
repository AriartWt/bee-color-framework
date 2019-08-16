<?php
namespace wfw\engine\package\general\handlers\action\zipCodes;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\general\data\zipCodes\IZipCodeModelAccess;
use wfw\engine\package\general\security\data\FindZipCode;

/**
 * Permet de trouver une liste de villes correspondant à un code postal
 */
final class FindHandler implements IActionHandler {
	/** @var IZipCodeModelAccess $_access */
	private $_access;
	/** @var FindZipCode $_rule */
	private $_rule;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * Find constructor.
	 *
	 * @param ITranslator         $translator
	 * @param IZipCodeModelAccess $access Accés au model de gestion des code postaux
	 * @param FindZipCode         $rule   Régle de validation des données
	 */
	public function __construct(
		ITranslator $translator,
		IZipCodeModelAccess $access,
		FindZipCode $rule
	) {
		$this->_translator = $translator;
		$this->_access = $access;
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if(!$action->getRequest()->isAjax()) return new ErrorResponse("404",
			$this->_translator->getAndReplace(
					"server/engine/core/app/404_NOT_FOUND",
					$action->getRequest()->getURI()
			)
		);
		$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
		$res = $this->_rule->applyTo($data);
		if($res->satisfied()){
			return new Response($this->_access->getCities(
				$data["country"]??"fr",$data["zipCode"]
			));
		}else return new ErrorResponse("201",$res->message(),$res->errors());
	}
}