<?php
namespace wfw\engine\package\lang\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Class TranslationPathRule
 *
 * @package wfw\engine\package\lang\security\data
 */
final class TranslationPathRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * TranslationPathRule constructor.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"Ce champ n'est pas valide !",
			new RequiredFields("Ce champ est requis !","lang_path"),
			new IsString("Ceci n'est pas une chaîne de caractères valide !","lang_path"),
			new MaxStringLength(
				"Ce champ ne peut excéder les 512 caractères de long !",
				512,
				"lang_path"
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