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
 * Class UploadPathRule
 *
 * @package wfw\engine\package\uploader\security\data
 */
final class PathsListRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * UploadPathRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxPathLength Taille maximale d'un chemin
	 * @param int         $maxPaths      Nombre maximum de chemins traités en une seule requête
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ITranslator $translator,
		int $maxPathLength = 2048,
		int $maxPaths = 10000
	) {
		$key = "server/engine/package/uploader/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"paths"),
			new IsArrayOf($translator->get("$key/INVALID_STRING"),function($d)use($maxPathLength){
				return is_string($d) && strlen($d) < $maxPathLength;
			},"paths"),
			new MaxArrayLength(
				$translator->getAndReplace("$key/MAX_ARRAY_LENGTH_REACHED",$maxPaths),
				$maxPaths,
				"paths"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport { return $this->_rule->applyTo($data); }
}