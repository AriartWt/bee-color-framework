<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\RequiredFields;
use wfw\engine\package\users\data\model\IUserModelAccess;

/**
 * Régle de validation des données pour la création d'un utilisateur
 */
final class RegisterUserRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RegisterUserRule constructor.
	 *
	 * @param ITranslator      $translator
	 * @param IUserModelAccess $access
	 * @param array            $roles
	 */
	public function __construct(ITranslator $translator,IUserModelAccess $access, array $roles=IsUserType::DEFAULT_TYPES) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"login","password","email","type"),
			new IsLogin($translator->get("$key/INVALID_LOGIN"),"login"),
			new IsUniqueLogin($access,$translator->get("$key/LOGIN_UNAVAILABLE"),"login"),
			new IsPassword($translator->get("$key/INVALID_PASSWORD"),"password"),
			new IsUserType($translator->getAndReplace(
				"$key/INVALID_USER_TYPE",implode(", ",$roles)),$roles,"type"
			),
			new IsEmail($translator->get("$key/INVALID_MAIL"),"email")
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