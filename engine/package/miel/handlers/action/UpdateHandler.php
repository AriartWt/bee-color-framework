<?php
namespace wfw\engine\package\miel\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\app\context\IWebAppContext;
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\miel\model\IMielModel;
use wfw\engine\package\miel\security\data\MielRule;

/**
 * Modifie la valeur d'une clé d'un MielPot
 */
final class UpdateHandler implements IActionHandler {
	/** @var IMielModel $_mielPot */
	private $_mielPot;
	/** @var MielRule $_rule */
	private $_rule;
	/** @var ICacheSystem $_cache */
	private $_cache;

	/**
	 * UpdateHandler constructor.
	 *
	 * @param IMielModel   $pot  MielPot à modifier
	 * @param MielRule     $rule Régle de validation des données postées
	 * @param ICacheSystem $cache
	 */
	public function __construct(
		IMielModel $pot,
		MielRule $rule,
		ICacheSystem $cache
	){
		$this->_mielPot = $pot;
		$this->_rule = $rule;
		$this->_cache = $cache;
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
				$this->_mielPot->set($data['miel_key'],$data['miel_data']);
				$this->_mielPot->save();
				$this->_cache->deleteAll([IWebAppContext::CACHE_KEYS[IWebAppContext::VIEWS]]);
				return new Response();
			}else{
				return new ErrorResponse("201",'Invalid data',$report->errors());
			}
		}else{
			return new ErrorResponse(404,"Page not found");
		}
	}
}