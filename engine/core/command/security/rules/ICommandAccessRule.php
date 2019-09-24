<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * Rule to check if a couple cmd is allowed.
 */
interface ICommandAccessRule {
	/**
	 * @param ICommand    $cmd
	 * @return null|bool True if the command can be run, false otherwise. Null if not applicable
	 */
	public function checkCommand(ICommand $cmd):?bool;
}