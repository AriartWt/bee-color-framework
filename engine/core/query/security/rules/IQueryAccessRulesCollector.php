<?php

namespace wfw\engine\core\query\security\rules;

/**
 * Check for rules to apply to querys
 */
interface IQueryAccessRulesCollector {
	/**
	 * @return IQueryAccessRule
	 */
	public function collect():IQueryAccessRule;
}