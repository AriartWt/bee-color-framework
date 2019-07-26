<?php

namespace wfw\engine\core\command\security;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\security\rules\ICommandAccessRule;

/**
 * Default security center. Will check if a command is allowed checking a CommandAccessRule.
 */
final class CommandSecurityCenter implements ICommandSecurityCenter {
	/** @var ICommandAccessRule $_rule */
	private $_rule;
	/** @var bool $_ignoredAsTrue */
	private $_ignoredAsTrue;

	/**
	 * CommandSecurityCenter constructor.
	 *
	 * @param ICommandAccessRule $rule
	 * @param bool               $ignoredAsTrue
	 */
	public function __construct(ICommandAccessRule $rule, bool $ignoredAsTrue = false) {
		$this->_rule = $rule;
		$this->_ignoredAsTrue = $ignoredAsTrue;
	}

	/**
	 * @param ICommand    $cmd    Command to run.
	 * @return bool True, the command is allowed for that user. False otherwise.
	 */
	public function allowCommand(ICommand $cmd): bool {
		return $this->_rule->checkCommand($cmd) ?? $this->_ignoredAsTrue;
	}
}