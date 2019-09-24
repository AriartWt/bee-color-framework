<?php

namespace wfw\engine\core\command\security\rules;

/**
 * Check for rules to apply to commands
 */
interface ICommandAccessRulesCollector {
	/**
	 * @return ICommandAccessRule
	 */
	public function collect():ICommandAccessRule;
}