<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Verifie les données nécessaire à un changement de type pour un utilisateur
 */
final class ChangeUserTypeRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ChangeUserTypeRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"L'un des champ est incorrect",
			new RequiredFields("Ce champe est requis !","id","type"),
			new IsUUID("Ceci n'est pas un identifiant valide !","id"),
			new IsUserType("Seuls client, basic et admin sont des types d'utilisateurs valides !","type")
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