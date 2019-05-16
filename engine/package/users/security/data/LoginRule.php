<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param ITranslator $translator
	 */
	public function __construct(ITranslator $translator) {
		$key = "server/engine/package/users/forms";
		$this->_mainRule = new AndRule(
			$translator->get("$key/LOGIN_FORM_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"login","password"),
			new IsString($translator->get("$key/LOGIN_FORM_ERROR"),"login","password"),
			new IsLogin($translator->get("$key/INVALID"),"login"),
			new OrRule(
				null,
				new IsPassword($translator->get("$key/INVALID_PASSWORD"),"password"),
				new IsUUID($translator->get("$key/INVALID_PASSWORD"),"password")
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