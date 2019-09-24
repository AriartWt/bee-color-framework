<?php

namespace wfw\engine\core\command\security;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\security\rules\AllCommandsDenied;
use wfw\engine\core\command\security\rules\ICommandAccessRule;
use wfw\engine\core\command\security\rules\ICommandAccessRulesCollector;

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
	 * @param ICommandAccessRulesCollector $collector
	 * @param bool                         $ignoredAsTrue
	 */
	public function __construct(ICommandAccessRulesCollector $collector, bool $ignoredAsTrue = false) {
		$this->_rule = ($collector) ? $collector->collect() : new AllCommandsDenied();
		$this->_ignoredAsTrue = $ignoredAsTrue ?? true;
	}

	/**
	 * @param ICommand    $cmd    Command to run.
	 * @return bool True, the command is allowed for that user. False otherwise.
	 */
	public function allowCommand(ICommand $cmd): bool {
		return $this->_rule->checkCommand($cmd) ?? $this->_ignoredAsTrue;
	}
}