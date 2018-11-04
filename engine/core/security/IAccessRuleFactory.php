<?php
namespace wfw\engine\core\security;

/**
 * Factory d'AccessRule
 */
interface IAccessRuleFactory {
	/**
	 * Créer une AccessRule
	 * @param string $ruleClass Classe de l'AccessRule à créer
	 * @param array  $params    Paramètres de construction
	 * @return IAccessRule
	 */
	public function create(string $ruleClass,array $params=[]):IAccessRule;
}