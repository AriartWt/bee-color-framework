<?php

namespace wfw\engine\core\query\security;

use wfw\engine\core\app\factory\IGenericAppFactory;
use wfw\engine\core\query\security\rules\IQueryAccessRule;

/**
 * Create access rules from a class and params with the app DIC.
 */
final class QueryAccessRuleFactory implements IQueryAccessRuleFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * QueryAccessRuleFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $ruleClass
	 * @param array  $params
	 * @return IQueryAccessRule
	 */
	public function create(string $ruleClass, array $params = []): IQueryAccessRule {
		return $this->_factory->create($ruleClass,$params,[IQueryAccessRule::class]);
	}
}