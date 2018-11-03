<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 11:05
 */

namespace wfw\engine\package\uploader\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\conf\IConf;
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
final class UploadFileHandler extends UploadHandler
{
	/**
	 * @var UploadFileRule $_rule
	 */
	private $_rule;

	/**
	 * UploadFileHandler constructor.
	 *
	 * @param IConf          $conf
	 * @param UploadFileRule $rule
	 */
	public function __construct(IConf $conf,UploadFileRule $rule) {
		parent::__construct($conf, null);
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse
	{
		if($action->getRequest()->isAjax() && $action->getRequest()->getMethod()===IRequest::POST){
			$data = $action->getRequest()->getData()->get(
				IRequestData::POST|IRequestData::FILES,
				true
			);
			$res = $this->_rule->applyTo($data);
			if($res->satisfied()){
				$maxFileSize = (new Byte($this->_conf->getString("server/uploader/max_size") ?? -1))->toInt();
				$quotas = (new Byte($this->_conf->getString("server/uploader/quotas") ?? -1))->toInt();
				$dirSize = $this->getUploadDirectorySize();
				if($quotas >= 0){ // si un quotas est défini
					if($maxFileSize >= 0){ // si une limite de taille pour les fichiers est définie
						if($dirSize + $maxFileSize > $quotas){ // si la taille du fichier + actuelle taille dépasse le quotas
							$maxFileSize = $quotas - ($dirSize + $maxFileSize); // le fichier ne peux pas dépasser l'espace restant
							if($maxFileSize <= 0) return new ErrorResponse(
								"201",
								"Vous n'avez plus assez d'espace disque disponible !"
							);
						}
					}else{
						$maxFileSize = $quotas - $dirSize; // si pas de limite fichier, la taille max est l'espace disponible
						if($maxFileSize <= 0) return new ErrorResponse(
							"201",
							"Vous n'avez plus assez d'espace disque disponible !"
						);
					}
				}
				//var_dump([$maxFileSize,$quotas,$dirSize,filesize($data["file"]["tmp_name"])]);
				if($maxFileSize < filesize($data["file"]["tmp_name"])) return new ErrorResponse(
					"201",
					"Vous n'avez plus assez d'espace disque disponible !"
				);
				try{
					$name = $this->realPath(strip_tags($data["name"]));
					if(!is_dir(dirname($name)))
						throw new \InvalidArgumentException("Unknown folder $name");
					move_uploaded_file($data["file"]["tmp_name"],$name);
					return new Response($this->getUploadFolderName().strip_tags($data["name"]));
				}catch (\InvalidArgumentException $e){
					return new ErrorResponse(201,$e->getMessage());
				}
			}else return new ErrorResponse(201,$res->message(),$res->errors());
		}else{
			return new ErrorResponse(404,"Page not found");
		}
	}
}