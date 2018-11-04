<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\AreEquals;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\RequiredFields;
use wfw\engine\package\users\data\model\IUserModelAccess;

/**
 * Verifie un formulaire d'inscription disponible publiquement pour les utilisateurs
 */
final class SelfRegisterRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * SelfRegisterRule constructor.
	 * @param IUserModelAccess $access
	 * @throws \InvalidArgumentException
	 */
	public function __construct(IUserModelAccess $access) {
		$this->_rule = new AndRule(
			new RequiredFields(
				"Ce champ est requis",
				"login","password","password_confirm","email","email_confirm",
				"agreement","phone"
			),
			new IsPassword("Ceci n'est pas un numéro de téléphone valide","phone"),
			new IsPassword("Ce mot de passe n'est pas valide","password","password_confirm"),
			new AreEquals("Les mots de passes ne sont pas identiques !","password","password_confirm"),
			new IsLogin("Ce login n'est pas valide", "login"),
			new IsUniqueLogin($access,"Ce login n'est pas disponible","login"),
			new IsEmail("Cet email n'est pas valide", "email","email_confirm"),
			new AreEquals("Les adresse mail ne sont pas identiques !","email","email_confirm")
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