<?php

namespace wfw\engine\core\query\security;

use wfw\engine\core\query\IQuery;
use wfw\engine\core\query\security\rules\IQueryAccessRule;

/**
 * Default security center. Will check if a query is allowed checking a QueryAccessRule.
 */
final class QuerySecurityCenter implements IQuerySecurityCenter {
	/** @var IQueryAccessRule $_rule */
	private $_rule;
	/** @var bool $_ignoredAsTrue */
	private $_ignoredAsTrue;

	/**
	 * QuerySecurityCenter constructor.
	 *
	 * @param IQueryAccessRule $rule
	 * @param bool               $ignoredAsTrue
	 */
	public function __construct(IQueryAccessRule $rule, bool $ignoredAsTrue = false) {
		$this->_rule = $rule;
		$this->_ignoredAsTrue = $ignoredAsTrue;
	}

	/**
	 * @param IQuery    $cmd    Query to run.
	 * @return bool True, the query is allowed for that user. False otherwise.
	 */
	public function allowQuery(IQuery $cmd): bool {
		return $this->_rule->checkQuery($cmd) ?? $this->_ignoredAsTrue;
	}
}