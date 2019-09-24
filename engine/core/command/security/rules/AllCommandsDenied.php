<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * Will deny all commands.
 */
final class AllCommandsDenied implements ICommandAccessRule {
	/**
	 * @param ICommand    $cmd
	 * @return null|bool True if the command can be run, false otherwise.
	 */
	public function checkCommand(ICommand $cmd): ?bool {
		return false;
	}
}