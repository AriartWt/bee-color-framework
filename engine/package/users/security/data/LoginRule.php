<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Regle de validation d'un formulaire de login
 */
final class LoginRule implements IRule {
	/** @var AndRule $_mainRule */
	private $_mainRule;

	/**
	 * LoginRule constructor.
	 */
	public function __construct() {
		$this->_mainRule = new AndRule(
			"Le login ou le mot de passe est incorrect",
			new RequiredFields("Ce champ est requis !","login","password"),
			new IsString("Ce champ est incorrect","login","password"),
			new IsLogin("Ceci n'est pas un login valide !","login"),
			new OrRule(
				null,
				new IsPassword("Ceci n'est pas un mot de passe valide !","password"),
				new IsUUID("Ceci n'est pas un mot de passe valide !","password")
			)
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