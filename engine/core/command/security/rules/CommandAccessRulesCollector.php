<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\security\ICommandAccessRuleFactory;

/**
 * Try to collect all rules that must be applyed to commands. Rules are collected in constructor.
 */
final class CommandAccessRulesCollector implements ICommandAccessRulesCollector {
	/** @var OrCommandAccessRule $_rule */
	private $_rule;

	/**
	 * CommandAccessAccessRulesCollector constructor.
	 *
	 * @param ICommandAccessRuleFactory $factory
	 * @param array                     $rules
	 */
	public function __construct(ICommandAccessRuleFactory $factory,array $rules=[]) {
		$rs = [];
		foreach($rules as $class=>$params){
			$rs[] = $factory->create($class,$params);
		}
		$this->_rule = new OrCommandAccessRule(...$rs);
	}

	/**
	 * @return ICommandAccessRule
	 */
	public function collect(): ICommandAccessRule {
		return $this->_rule;
	}
}