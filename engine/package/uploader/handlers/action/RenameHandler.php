<?php
namespace wfw\engine\package\uploader\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\uploader\security\data\RenamePathRule;

/**
 * Renome un fichier ou un dossier
 */
final class RenameHandler extends UploadHandler {
	/** @var RenamePathRule $_rule */
	private $_rule;

	/**
	 * RenameHandler constructor.
	 *
	 * @param IConf          $conf Configurations du site
	 * @param ITranslator    $translator
	 * @param RenamePathRule $rule Régle de validation
	 */
	public function __construct(IConf $conf, ITranslator $translator, RenamePathRule $rule) {
		parent::__construct($conf, $translator,null);
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
				try{
					$oldPaths = $data["oldPaths"];
					$newPaths = $data["newPaths"];
					$newNames = [];
					if(count($oldPaths)===count($newPaths) && count($oldPaths) > 0){
						for($i=0;$i<count($oldPaths);$i++){
							$newNames[$oldPaths[$i]] = $nName = $this->sanitize($newPaths[$i]);
							rename(
								$this->realPath(strip_tags($oldPaths[$i])),
								$this->realPath($nName)
							);
						}
						return new Response($newNames);
					}else return new ErrorResponse("201",$this->_translator->get(
						"server/engine/package/uploader/RENAME_LIST_ERROR"
					));
				}catch(\InvalidArgumentException $e){
					return new ErrorResponse(201,$e->getMessage());
				}
			}else return new ErrorResponse(201,$res->message(),$res->errors());
		}else return new ErrorResponse(404,$this->_translator->getAndReplace(
			"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
		));
	}
}