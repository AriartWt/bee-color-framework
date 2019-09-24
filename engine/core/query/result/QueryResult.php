<?php

namespace wfw\engine\core\query\result;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Base query result
 */
abstract class QueryResult implements IQueryResult {
	/** @var UUID $_id */
	private $_id;
	/** @var UUID $_queryId */
	private $_queryId;
	/** @var float $_creationDate */
	private $_creationDate;

	/**
	 * QueryResult constructor.
	 *
	 * @param UUID $queryId Query that ask for the result.
	 */
	public function __construct(UUID $queryId) {
		$this->_id = new UUID(UUID::V4);
		$this->_creationDate = microtime(true);
		$this->_queryId = $queryId;
	}

	/**
	 *  QueryResult ID
	 *
	 * @return UUID
	 */
	public function getUUID(): UUID {
		return $this->_id;
	}

	/**
	 * Query id that ask for a result
	 *
	 * @return UUID
	 */
	public function getQueryId(): UUID {
		return $this->_queryId;
	}

	/**
	 * Result creation date
	 *
	 * @return float
	 */
	public function getGenerationDate(): float {
		return $this->_creationDate;
	}
}