<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle validant un formulaire de reset de mot de passe.
 */
final class RetrievePasswordRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RetrievePasswordRule constructor.
	 *
	 * @param ITranslator $translator
	 */
	public function __construct(ITranslator $translator) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"login"),
			new IsLogin($translator->get("$key/INVALID_LOGIN"),"login")
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