<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Regle de confirmation des données GET pour les handlers ResetPassword et ChangeMailConfirmation
 */
final class ConfirmRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ConfirmRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"L'une de ces informations est invalide",
			new RequiredFields("Ce champ est obligatoire","id","code"),
			new IsUUID("Ceci n'est pas un identifiant valide","id","code")
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