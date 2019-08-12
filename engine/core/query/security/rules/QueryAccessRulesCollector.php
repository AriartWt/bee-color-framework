<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\security\IQueryAccessRuleFactory;

/**
 * Try to collect all rules that must be applyed to commands. Rules are collected in constructor.
 */
final class QueryAccessRulesCollector implements IQueryAccessRulesCollector {
	/** @var OrQueryAccessRule $_rule */
	private $_rule;

	/**
	 * QueryAccessAccessRulesCollector constructor.
	 *
	 * @param IQueryAccessRuleFactory $factory
	 * @param array                     $rules
	 */
	public function __construct(IQueryAccessRuleFactory $factory,array $rules=[]) {
		$rs = [];
		foreach($rules as $class=>$params){
			$rs[] = $factory->create($class,$params);
		}
		$this->_rule = new OrQueryAccessRule(...$rs);
	}

	/**
	 * @return IQueryAccessRule
	 */
	public function collect(): IQueryAccessRule {
		return $this->_rule;
	}
}