<?php

namespace wfw\engine\core\action;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * ActionHookFactory
 */
final class ActionHookFactory implements IActionHookFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * ActionHookFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $class  Class that implements IActionHook
	 * @param array  $params ActionHook params
	 * @return IActionHook
	 */
	public function create(string $class, array $params = []): IActionHook {
		return $this->_factory->create($class,$params,[IActionHook::class]);
	}
}