<?php

namespace wfw\engine\core\action;

/**
 * Can create an ActionHook
 */
interface IActionHookFactory {
	/**
	 * @param string $class  Class that implements IActionHook
	 * @param array  $params ActionHook params
	 * @return IActionHook
	 */
	public function create(string $class,array $params=[]):IActionHook;
}