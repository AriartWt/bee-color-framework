<?php

namespace wfw\engine\core\command\security;

use wfw\engine\core\command\security\rules\ICommandAccessRule;

/**
 * Allow to create access rules
 */
interface ICommandAccessRuleFactory {
	/**
	 * @param string $ruleClass
	 * @param array  $params
	 * @return ICommandAccessRule
	 */
	public function create(string $ruleClass,array $params=[]):ICommandAccessRule;
}