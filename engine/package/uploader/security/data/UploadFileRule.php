<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 11:07
 */

namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsFile;
use wfw\engine\core\security\data\rules\IsString;
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
	 * @param IConf $conf
	 */
	public function __construct(IConf $conf) {
		$maxFileSize = (new Byte($conf->getString("server/uploader/max_size") ?? -1))->toInt();
		$this->_rule = new AndRule(
			"Les données sont invalides",
			new RequiredFields("Ces champs sont requis : ","file","name"),
			new IsString("Ce champ n'est as une chaine valide","name"),
			new IsFile(
				"Ce fichier est invalide ou trop volumineux !",
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