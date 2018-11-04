<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Verifie la présence et la validité d'un champ "id"
 */
final class UserIdRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * UserIdRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"Certaines informations sont incorrectes",
			new RequiredFields("Ce champ est requis","id"),
			new IsUUID("Ceci n'est pas un identifiant valide !","id")
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