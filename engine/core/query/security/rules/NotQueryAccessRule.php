<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\IQuery;

/**
 * Class NotQueryAccessRule
 *
 * @package wfw\engine\core\query\security\rules
 */
final class NotQueryAccessRule implements IQueryAccessRule {
	/** @var IQueryAccessRule $_rule */
	private $_rule;

	/**
	 * NotQueryAccessRule constructor.
	 *
	 * @param IQueryAccessRule $rule Rule to negate
	 */
	public function __construct(IQueryAccessRule $rule) {
		$this->_rule = $rule;
	}

	/**
	 * @param IQuery $cmd
	 * @return null|bool True if the query can be run, false otherwise. Null if not applicable
	 */
	public function checkQuery(IQuery $cmd): ?bool {
		return !$this->_rule->checkQuery($cmd);
	}
}