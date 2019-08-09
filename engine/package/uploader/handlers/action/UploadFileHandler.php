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
				foreach($data["files"] as $file){
					$totalSize += filesize($file["tmp_name"]);
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
					foreach($data["names"] as $name){
						$fname = $this->sanitize($name);
						$name = $this->realPath($fname);
						if(!is_dir(dirname($name)))
							throw new \InvalidArgumentException("Unknown folder $name");
						move_uploaded_file($data["file"]["tmp_name"],$name);
						$res[] = $this->getUploadFolderName().$fname;
						return new Response($this->getUploadFolderName().$fname);
					}
					return new Response($res);
				}catch (\Error | \Exception $e){
					foreach($res as $file) if(file_exists($file)) unlink($file);
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