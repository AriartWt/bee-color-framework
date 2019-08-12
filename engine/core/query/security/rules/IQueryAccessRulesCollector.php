<?php

namespace wfw\engine\core\query\security\rules;

/**
 * Check for rules to apply to commands
 */
interface IQueryAccessRulesCollector {
	/**
	 * @return IQueryAccessRule
	 */
	public function collect():IQueryAccessRule;
}