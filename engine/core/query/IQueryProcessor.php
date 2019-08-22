<?php
namespace wfw\engine\core\query;

/**
 *  Recieve request and send them to their handler
 */
interface IQueryProcessor {
	/**
	 *  Redirect the query to its handler
	 *
	 * @param IQuery $query query to redirect
	 */
	public function processQuery(IQuery $query):void;
}