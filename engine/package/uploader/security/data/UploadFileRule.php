<?php
namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsFile;
use wfw\engine\core\security\data\rules\MatchRegexp;
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
		$maxFileSize = (new Byte($conf->getString("server/uploader/max_size") ?? -1))->toInt();
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"file","name"),
			new MatchRegexp(
				"/^.{1,$maxFileNameLength}$/",
			$translator->getAndReplace("$key/INVALID_FILE_NAME",$maxFileNameLength),
				"name"
			),
			new IsFile(
				$translator->get("$key/INVALID_FILE"),
				$maxFileSize,
				$conf->getArray("server/uploader/accepted_mimes") ?? ["/^image\/.*$/","/^video\/.*"],
				"file"
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