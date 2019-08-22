<?php
namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsArrayOf;
use wfw\engine\core\security\data\rules\IsFileArray;
use wfw\engine\core\security\data\rules\RequiredFields;
use wfw\engine\lib\PHP\types\Byte;

/**
 * Régle de validation des fichiers à uploader
 */
final class UploadFileRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * UploadFileRule constructor.
	 *
	 * @param IConf       $conf
	 * @param ITranslator $translator
	 * @param int         $maxFileNameLength Taille maximale d'un nom de fichier
	 */
	public function __construct(IConf $conf,ITranslator $translator,int $maxFileNameLength = 512) {
		$key = "server/engine/package/uploader/forms";
		$maxFileSize = (new Byte($conf->getString("server/uploader/max_size_by_file") ?? -1))->toInt();
		$totalMaxFileSize = (new Byte($conf->getString("server/uploader/max_size_at_once") ?? -1))->toInt();
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"files","names"),
			new IsArrayOf($translator->get("$key/INVALID_FILE_NAME"),function($fname)use($maxFileNameLength){
				return is_string($fname) && preg_match("/^.{1,$maxFileNameLength}$/",$fname);
			},"names"),
			new IsFileArray(
				$translator->get("$key/INVALID_FILE"),
				$maxFileSize,
				$totalMaxFileSize,
				$conf->getArray("server/uploader/accepted_mimes") ?? ["/^image\/.*$/","/^video\/.*"],
				"files"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		return $this->_rule->applyTo($data);
	}
}