<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/07/18
 * Time: 14:06
 */

namespace wfw\engine\package\general\handlers\action\zipCodes;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
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

	/**
	 * Find constructor.
	 *
	 * @param IZipCodeModelAccess $access Accés au model de gestion des code postaux
	 * @param FindZipCode         $rule Régle de validation des données
	 */
	public function __construct(IZipCodeModelAccess $access,FindZipCode $rule) {
		$this->_access = $access;
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if(!$action->getRequest()->isAjax()) return new ErrorResponse("404","Not found");
		$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
		$res = $this->_rule->applyTo($data);
		if($res->satisfied()){
			return new Response($this->_access->getCities(
				$data["country"]??"france",$data["zipCode"]
			));
		}else return new ErrorResponse("201",$res->message(),$res->errors());
	}
}