<?php
namespace wfw\engine\core\query;

/**
 *  Permet de traiter une querye
 */
interface IQueryHandler {
	/**
	 * handle a query
	 * @param IQuery $query Querye à traiter
	 */
	public function handleQuery(IQuery $query);
}