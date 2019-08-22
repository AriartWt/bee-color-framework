<?php

namespace wfw\engine\core\command\security;

use wfw\engine\core\app\factory\IGenericAppFactory;
use wfw\engine\core\command\security\rules\ICommandAccessRule;

/**
 * Create access rules from a class and params with the app DIC.
 */
final class CommandAccessRuleFactory implements ICommandAccessRuleFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * CommandAccessRuleFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $ruleClass
	 * @param array  $params
	 * @return ICommandAccessRule
	 */
	public function create(string $ruleClass, array $params = []): ICommandAccessRule {
		return $this->_factory->create($ruleClass,$params,[ICommandAccessRule::class]);
	}
}