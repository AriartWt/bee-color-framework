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
use wfw\engine\package\uploader\security\data\PathsListRule;

/**
 * Permet de supprimer un/plusieurs fichiers/repertoirs
 */
final class DeleteHandler extends UploadHandler {
	/** @var PathsListRule $_rule */
	private $_rule;

	/**
	 * DeleteHandler constructor.
	 *
	 * @param IConf         $conf
	 * @param ITranslator   $translator
	 * @param PathsListRule $rule
	 */
	public function __construct(IConf $conf, ITranslator $translator, PathsListRule $rule) {
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
				$paths = $data["paths"];
				try{
					foreach($paths as $path){
						$rpath = $this->realPath($path);
						if(is_file($rpath)) unlink($rpath);
						else if(is_dir($rpath)) $this->rmDir($rpath);
					}
					return new Response();
				}catch(\InvalidArgumentException $e){
					return new ErrorResponse(201,$e->getMessage());
				}
			}else return new ErrorResponse(201,$res->message(),$res->errors());
		}else{
			return new ErrorResponse(404,$this->_translator->getAndReplace(
				"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
			));
		}
	}

	/**
	 * @param string $path Chemin d'accés au dossier à supprimer.
	 */
	private function rmDir(string $path):void{
		if(!is_dir($path))
			throw new \InvalidArgumentException("$path is not a valid directory !");
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$path,
				\RecursiveDirectoryIterator::SKIP_DOTS
			),\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $el){
			/** @var \SplFileInfo $el */
			if($el->isFile() || $el->isLink()) unlink($el->getPathname());
			else if($el->isDir()) rmdir($el->getPathname());
		}
		rmdir($path);
	}
}