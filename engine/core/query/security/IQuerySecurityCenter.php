<?php

namespace wfw\engine\core\query\security;

use wfw\engine\core\query\IQuery;

/**
 * Will check if a user (or anonymous user) is allowed to run a query.
 */
interface IQuerySecurityCenter {
	/**
	 * @param IQuery    $cmd    Query to run.
	 * @return bool True, the query is allowed for that user. False otherwise.
	 */
	public function allowQuery(IQuery $cmd):bool;
}