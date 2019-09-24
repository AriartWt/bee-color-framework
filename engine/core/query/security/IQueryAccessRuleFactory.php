<?php

namespace wfw\engine\core\query\security;

use wfw\engine\core\query\security\rules\IQueryAccessRule;

/**
 * Allow to create access rules
 */
interface IQueryAccessRuleFactory {
	/**
	 * @param string $ruleClass
	 * @param array  $params
	 * @return IQueryAccessRule
	 */
	public function create(string $ruleClass,array $params=[]):IQueryAccessRule;
}