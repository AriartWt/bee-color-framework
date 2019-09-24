<?php
namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsArrayOf;
use wfw\engine\core\security\data\rules\MaxArrayLength;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation pour des données en vue d'un renommage de fichier/dossier.
 */
final class RenamePathRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RenamePathRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxPathLength Taille maximale d'un chemin
	 * @param int         $maxPath       Nombre de chemins traités simultanément
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ITranslator $translator,
		int $maxPathLength = 2048,
		int $maxPath = 10000
	) {
		$key = "server/engine/package/uploader/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"oldPaths","newPaths"),
			new IsArrayOf($translator->get("$key/INVALID_STRING"),function($d)use($maxPathLength){
				return is_string($d) && strlen($d)<$maxPathLength;
			},"oldPaths","newPaths"),
			new MaxArrayLength(
				$translator->getAndReplace("$key/MAX_ARRAY_LENGTH_REACHED",$maxPath),
				$maxPath,
				"oldPaths","newPaths"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport { return $this->_rule->applyTo($data); }
}