<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param ITranslator $translator
	 */
	public function __construct(ITranslator $translator) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"id"),
			new IsUUID($translator->get("$key/INVALID_ID"),"id")
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