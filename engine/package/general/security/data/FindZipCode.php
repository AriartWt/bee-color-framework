<?php
namespace wfw\engine\package\general\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsEmpty;
use wfw\engine\core\security\data\rules\MatchRegexp;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Permet de valider les données nécessaires à
 */
class FindZipCode implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * FindZipCode constructor.
	 *
	 * @param ITranslator $translator
	 * @param string      $regexp Expression régulière pour valider les codes postaux
	 */
	public function __construct(ITranslator $translator,string $regexp = "/^[0-9]{5}$/") {
		$key = "server/engine/package/general/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"zipCode"),
			new MatchRegexp(
				$regexp,
				$translator->get("$key/INVALID_ZIP_CODE"),
				"zipCode"
			),
			new OrRule(
				null,
				new IsEmpty("country"),
				new MatchRegexp(
					"/^[a-zA-Z]{2,120}$/",
					$translator->get("$key/INVALID_COUNTRY_NAME"),
					"country"
				)
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