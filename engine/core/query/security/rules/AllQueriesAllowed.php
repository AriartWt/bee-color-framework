<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\IQuery;

/**
 * All queries are allowed.
 */
final class AllQueriesAllowed implements IQueryAccessRule{
	/**
	 * @param IQuery    $cmd
	 * @return null|bool True if the query can be run, false otherwise.
	 */
	public function checkQuery(IQuery $cmd): ?bool {
		return true;
	}
}