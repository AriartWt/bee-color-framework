<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\IQuery;

/**
 * If any of the given rules is true, the result is true.
 */
class OrQueryAccessRule implements IQueryAccessRule{
	/** @var IQueryAccessRule[] $_rules */
	private $_rules;

	/**
	 * OrQueryAccessRule constructor.
	 *
	 * @param IQueryAccessRule ...$rules
	 */
	public function __construct(IQueryAccessRule ...$rules) {
		$this->_rules = $rules;
	}

	/**
	 * @param IQuery    $cmd
	 * @return null|bool True if the query can be run, false otherwise.
	 */
	public function checkQuery(IQuery $cmd): ?bool {
		if(count($this->_rules) === 0) return null;
		$unapplicable = 0;
		foreach($this->_rules as $rule){
			if($res = $rule->checkQuery($cmd)) return true;
			else if(is_null($res)) $unapplicable++;
		}
		if($unapplicable === count($this->_rules)) return null;
		return false;
	}
}