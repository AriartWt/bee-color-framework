<?php

namespace wfw\engine\core\query\result;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Result to a query, generated by a QueryHandler.
 */
interface IQueryResult {
	/**
	 *  QueryResult ID
	 * @return UUID
	 */
	public function getUUID():UUID;

	/**
	 * Query id that ask for a result
	 * @return UUID
	 */
	public function getQueryId():UUID;

	/**
	 * Result creation date
	 * @return float
	 */
	public function getGenerationDate():float;
}