<?php
namespace wfw\engine\package\uploader\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\uploader\security\data\PathsListRule;

/**
 * Crée un ou plusieurs dossiers
 */
final class CreatePathHandler extends UploadHandler {
	/** @var PathsListRule $_rule */
	private $_rule;

	/**
	 * DeleteHandler constructor.
	 *
	 * @param IConf         $conf
	 * @param PathsListRule $rule
	 */
	public function __construct(IConf $conf, PathsListRule $rule) {
		parent::__construct($conf, null);
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax() && $action->getRequest()->getMethod()===IRequest::POST){
			$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
			$res = $this->_rule->applyTo($data);
			if($res->satisfied()){
				$paths = $data["paths"];
				try{
					foreach($paths as $path){mkdir($this->realPath(strip_tags($path)));}
					return new Response();
				}catch(\InvalidArgumentException $e){
					return new ErrorResponse(201,$e->getMessage());
				}catch(\Exception $e){
					return new ErrorResponse(500,$e->getMessage());
				}
			}else return new ErrorResponse(201,$res->message(),$res->errors());
		}else{
			return new ErrorResponse(404,"Page not found");
		}
	}
}