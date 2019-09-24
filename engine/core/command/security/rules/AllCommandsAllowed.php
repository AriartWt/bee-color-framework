<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * All commands are allowed.
 */
final class AllCommandsAllowed implements ICommandAccessRule{
	/**
	 * @param ICommand    $cmd
	 * @return null|bool True if the command can be run, false otherwise.
	 */
	public function checkCommand(ICommand $cmd): ?bool {
		return true;
	}
}