<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\AreEquals;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Verifie un formulaire de changement de mot de passe.
 */
final class ChangePasswordRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ChangePasswordRule constructor.
	 *
	 * @param ITranslator $translator
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ITranslator $translator) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"password","password_confirm","old"),
			new AreEquals($translator->get("$key/NOT_SAME_PASSWORD"),"password","password_confirm"),
			new IsPassword($translator->get("$key/INVALID_PASSWORD"),"password","password_confirm","old")
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