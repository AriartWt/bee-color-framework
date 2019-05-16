<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param ITranslator $translator
	 * @param array       $validRoles
	 */
	public function __construct(ITranslator $translator, array $validRoles=IsUserType::DEFAULT_TYPES) {
		$key = "server/engine/package/users/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"id","type"),
			new IsUUID($translator->get("$key/INVALID_ID"),"id"),
			new IsUserType(
				$translator->getAndReplace(
					"$key/INVALID_USER_TYPE",implode(", ",$validRoles)
				),
				$validRoles,"type"
			)
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