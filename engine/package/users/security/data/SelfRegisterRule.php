<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param IUserModelAccess $access
	 * @param ITranslator      $translator
	 * @throws \InvalidArgumentException
	 */
	public function __construct(IUserModelAccess $access, ITranslator $translator) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			new RequiredFields(
				$translator->get("$key/REQUIRED"),
				"login","password","password_confirm","email","email_confirm",
				"agreement","phone"
			),
			new IsPassword($translator->get("$key/INVALID_PASSWORD"),"password","password_confirm"),
			new AreEquals($translator->get("$key/NOT_SAME_PASSWORD"),"password","password_confirm"),
			new IsLogin($translator->get("$key/INVALID_LOGIN"), "login"),
			new IsUniqueLogin($access,$translator->get("$key/LOGIN_UNAVAILABLE"),"login"),
			new IsEmail($translator->get("$key/INVALID_MAIL"), "email","email_confirm"),
			new AreEquals($translator->get("$key/NOT_SAME_MAIL"),"email","email_confirm")
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