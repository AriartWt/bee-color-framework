<?php

namespace wfw\engine\core\query\security;

use wfw\engine\core\query\IQuery;

/**
 * Will check if a user (or anonymous user) is allowed to run a command.
 */
interface IQuerySecurityCenter {
	/**
	 * @param IQuery    $cmd    Command to run.
	 * @return bool True, the command is allowed for that user. False otherwise.
	 */
	public function allowCommand(IQuery $cmd):bool;
}