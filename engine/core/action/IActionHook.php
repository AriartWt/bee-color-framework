<?php

namespace wfw\engine\core\action;

use wfw\engine\core\response\IResponse;
use wfw\engine\core\security\IAccessPermission;

/**
 * Action hook called right after the AccessControlCenter check.
 * An ActionHook response will be a priority and be treated immediately. It bypasses the app ActionRouter
 * routine to immediately process the response. (Usefull for redirections)
 */
interface IActionHook {
	/**
	 * @param IAction           $action User action
	 * @param IAccessPermission $permission User permission
	 * @return null|IResponse Response
	 */
	public function hook(IAction $action, IAccessPermission $permission):?IResponse;
}