<?php

namespace wfw\engine\core\command\security;

use wfw\engine\core\command\ICommand;

/**
 * Will check if a user (or anonymous user) is allowed to run a command.
 */
interface ICommandSecurityCenter {
	/**
	 * @param ICommand    $cmd    Command to run.
	 * @return bool True, the command is allowed for that user. False otherwise.
	 */
	public function allowCommand(ICommand $cmd):bool;
}