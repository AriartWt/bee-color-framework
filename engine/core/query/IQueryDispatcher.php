<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\errors\NoQueryHandlerFound;

/**
 * Dispatcher de queryes
 */
interface IQueryDispatcher {
	/**
	 * @param IQuery $query Querye à dispatcher
	 * @throws NoQueryHandlerFound
	 */
	public function dispatchQuery(IQuery $query):void;
}