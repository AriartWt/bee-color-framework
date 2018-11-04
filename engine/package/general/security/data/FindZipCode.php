<?php
namespace wfw\engine\package\general\security\data;

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
	 * @param string $regexp Expression régulière pour valider les codes postaux
	 */
	public function __construct(string $regexp = "/^[0-9]{5}$/") {
		$this->_rule = new AndRule(
			"Les données fournies sont incorrectes",
			new RequiredFields("Ce champ est requis !","zipCode"),
			new MatchRegexp(
				$regexp,
				"Ceci n'est pas un code postal valide !",
				"zipCode"
			),
			new OrRule(
				null,
				new IsEmpty("country"),
				new MatchRegexp(
					"/^[a-zA-Z]{2,120}$/",
					"Ceci n'est pas un nom de pays valide !",
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