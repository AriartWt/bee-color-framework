<?php
namespace wfw\engine\core\security;

use wfw\engine\core\action\IAction;

/**
 * Interface IAccessRule
 *
 * @package wfw\engine\core\security
 */
interface IAccessRule {
	/**
	 * @param IAction $action Action à tester
	 * @return null|IAccessPermission Si null, action autorisée et interruption de la chaine des
	 * vérifications.
	 */
	public function check(IAction $action):?IAccessPermission;
}