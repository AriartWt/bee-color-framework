<?php
namespace wfw\engine\package\miel\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\NotEmpty;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation de base pour la fonctionnalité miel.
 */
final class MielRule implements IRule {
	/** @var AndRule $_mainRule */
	private $_mainRule;

	/**
	 * MielRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxDataLength
	 * @param int         $maxKeyLength
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ITranslator $translator,
		int $maxDataLength=500000,
		int $maxKeyLength=512
	) {
		$key = "server/engine/package/miel/forms";
		$this->_mainRule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields(
				$translator->get("$key/REQUIRED"),"miel_key","miel_data"
			),
			new MaxStringLength(
				$translator->getAndReplace("$key/TOO_LARGE_KEY",$maxKeyLength),
				$maxKeyLength,
				"miel_key"
			),
			new MaxStringLength(
				$translator->getAndReplace("$key/TOO_LARGE_STRING",$maxDataLength),
				$maxDataLength,
				"miel_data"
			),
			new NotEmpty($translator->get("$key/NOT_EMPTY"),"miel_key")
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		return $this->_mainRule->applyTo($data);
	}
}