<?php
namespace wfw\engine\package\uploader\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;

/**
 * Liste tous les fichiers et repertoirs du dossier uploads.
 */
final class ListHandler extends UploadHandler {
	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()){
			$folder = $this->getUploadFolderPath();
			if(!is_dir($folder))
				return new ErrorResponse(
					500,
					"No upload folder defined, or specified folder didn't exists !"
				);
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(
					$folder,
					\RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);
			$uploadFolder = $this->getUploadFolderName();
			$arr = [];
			foreach($iterator as $el){
				/** @var \SplFileInfo $el */
				$path = str_replace("$folder/",'',$el->getPathname());
				$tmp = explode('/',$path);
				if($tmp !== ''){
					$array = &$arr;
					$last = array_pop($tmp);
					foreach($tmp as &$t){
						if(!empty($t) && !isset($array[$t])){
							$array[$t] = [ "items" => [] ];
						}
						$array = &$array[$t]["items"];
					}
					$array[$last]=[
						"type" => $el->getType(),
						"name" => $last,
						"path" => "$uploadFolder/$path",
						"mtime" => $el->getMTime(),
						"ctime" => $el->getCTime()
					];
					if($el->isDir()){
						$array[$last]["items"]=[];
					}else{
						$array[$last]["size"] = $el->getSize();
						$array[$last]["mime"] = mime_content_type($el->getPathname());
					}
				}
			}
			return new Response($arr);
		}else{
			return new ErrorResponse(404,$this->_translator->getAndReplace(
				"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
			));
		}
	}
}