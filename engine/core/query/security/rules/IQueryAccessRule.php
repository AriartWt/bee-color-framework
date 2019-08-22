<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\IQuery;

/**
 * Rule to check if a couple query is allowed.
 */
interface IQueryAccessRule {
	/**
	 * @param IQuery    $cmd
	 * @return null|bool True if the query can be run, false otherwise. Null if not applicable
	 */
	public function checkQuery(IQuery $cmd):?bool;
}