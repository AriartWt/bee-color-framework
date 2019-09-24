<?php

namespace wfw\engine\core\query\security\rules;

use wfw\engine\core\query\IQuery;

/**
 * Will deny all querys.
 */
final class AllQueriesDenied implements IQueryAccessRule {
	/**
	 * @param IQuery    $cmd
	 * @return null|bool True if the query can be run, false otherwise.
	 */
	public function checkQuery(IQuery $cmd): ?bool {
		return false;
	}
}