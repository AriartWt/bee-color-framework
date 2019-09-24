<?php

namespace wfw\engine\core\action;

use wfw\engine\core\response\IResponse;
use wfw\engine\core\security\IAccessPermission;

/**
 * Run all ActionHooks in order. Stop and return the first IResponse it gets.
 * ex :
 * Hook1 -> null
 * Hook2 -> null
 * Hook3 -> Redirection
 * Hook4 -> not executed.
 */
final class MultiHook implements IActionHook {
	/** @var IActionHook[] $_hooks */
	private $_hooks;

	/**
	 * MultiHook constructor.
	 *
	 * @param IActionHookFactory $factory
	 * @param array              $hooks class=>[params]
	 */
	public function __construct(IActionHookFactory $factory, array $hooks) {
		$this->_hooks = [];
		foreach ($hooks as $class=>$params){
			$this->_hooks[] = $factory->create($class,$params);
		}
	}

	/**
	 * @param IAction           $action     User action
	 * @param IAccessPermission $permission User permission
	 * @return null|IResponse Response
	 */
	public function hook(IAction $action, IAccessPermission $permission): ?IResponse {
		foreach($this->_hooks as $hook){
			$resp = $hook->hook($action,$permission);
			if(!is_null($resp)) return $resp;
		}
		return null;
	}
}