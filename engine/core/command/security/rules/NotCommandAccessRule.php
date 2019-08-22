<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * Class NotCommandAccessRule
 *
 * @package wfw\engine\core\command\security\rules
 */
final class NotCommandAccessRule implements ICommandAccessRule {
	/** @var ICommandAccessRule $_rule */
	private $_rule;

	/**
	 * NotCommandAccessRule constructor.
	 *
	 * @param ICommandAccessRule $rule Rule to negate
	 */
	public function __construct(ICommandAccessRule $rule) {
		$this->_rule = $rule;
	}

	/**
	 * @param ICommand $cmd
	 * @return null|bool True if the command can be run, false otherwise. Null if not applicable
	 */
	public function checkCommand(ICommand $cmd): ?bool {
		return !$this->_rule->checkCommand($cmd);
	}
}