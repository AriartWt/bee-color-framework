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
use wfw\engine\lib\PHP\types\Byte;
use wfw\engine\package\uploader\security\data\UploadFileRule;

/**
 * Permet d'uploader un fichier vers le dossiers upoloads.
 */
final class UploadFileHandler extends UploadHandler {
	/** @var UploadFileRule $_rule */
	private $_rule;

	/**
	 * UploadFileHandler constructor.
	 *
	 * @param IConf          $conf
	 * @param ITranslator    $translator
	 * @param UploadFileRule $rule
	 */
	public function __construct(IConf $conf, ITranslator $translator, UploadFileRule $rule) {
		parent::__construct($conf, $translator,null);
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax() && $action->getRequest()->getMethod()===IRequest::POST){
			$data = $action->getRequest()->getData()->get(
				IRequestData::POST|IRequestData::FILES,
				true
			);
			$res = $this->_rule->applyTo($data);
			if($res->satisfied()){
				$totalSize = 0;
				foreach($data["files"]["size"] as $size){
					$totalSize += $size;
				}
				$quotas = (new Byte($this->_conf->getString("server/uploader/quotas") ?? -1))->toInt();
				$dirSize = $this->getUploadDirectorySize();
				if($quotas >= 0){ // si un quotas est défini
					$maxFileSize = $quotas - $dirSize; // si pas de limite fichier, la taille max est l'espace disponible
					if($maxFileSize < $totalSize) return new ErrorResponse(
						"201",
						$this->_translator->getAndReplace("server/engine/package/uploader/STORAGE_QUOTA_EXCEED")
					);
				}
				try{
					$res = [];
					$alreadyExists = [];
					foreach($data["names"] as $k=>$name){
						$fname = $this->sanitize($name);
						$name = $this->realPath($fname);
						if(!is_dir(dirname($name)))
							throw new \InvalidArgumentException("Unknown folder $name");
						if(is_file($name)) $alreadyExists[] = $fname;
						else $res[$data["files"]["tmp_name"][$k]] = [
							"new_name" => $name,
							"return_value" => $this->getUploadFolderName().$fname,
							"tmp_name" => $data["files"]["tmp_name"][$k]
						];
					}
					if(count($alreadyExists) > 0) throw new \InvalidArgumentException(
						$this->_translator->getAndReplace(
							"server/engine/package/uploader/FILE_ALREADY_EXISTS",
							implode("\n",$alreadyExists)
						)
					);
					$toSend = [];
					foreach($res as $k=>$v){
						move_uploaded_file($v["tmp_name"],$v["new_name"]);
						$toSend[] = $v["return_value"];
					}
					return new Response($toSend);
				}catch (\Error | \Exception $e){
					return new ErrorResponse(201,$e->getMessage());
				}
			}else return new ErrorResponse(201,$res->message(),$res->errors());
		}else{
			return new ErrorResponse(404,$this->_translator->getAndReplace(
				"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
			));
		}
	}
}