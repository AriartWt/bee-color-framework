<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/10/18
 * Time: 17:42
 */

namespace wfw\engine\package\miel\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\miel\model\IMielModel;
use wfw\engine\package\miel\security\data\MielRule;

/**
 * Permet de récupérer la valeur d'une clé
 */
final class ReadHandler implements IActionHandler{
	/** @var IMielModel $_mielPot */
	private $_mielPot;
	/** @var MielRule $_rule */
	private $_rule;

	/**
	 * ReadHandler constructor.
	 *
	 * @param IMielModel $pot  MielPot à modifier
	 * @param MielRule   $rule Régle de validation des données postées
	 */
	public function __construct(
		IMielModel $pot,
		MielRule $rule
	){
		$this->_mielPot = $pot;
		$this->_rule = $rule;
	}
	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()){
			$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
			$report = $this->_rule->applyTo($data);
			if($report->satisfied()){
				return new Response($this->_mielPot->get($data['miel_key']));
			}else{
				return new ErrorResponse("201",'Invalid data',$report->errors());
			}
		}else{
			return new ErrorResponse(404,"Page not found");
		}
	}
}