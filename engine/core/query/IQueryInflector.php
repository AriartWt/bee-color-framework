<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\errors\NoQueryHandlerFound;

/**
 *  Permet de trouver un handler pour une querye
 */
interface IQueryInflector {
	/**
	 *  Trouve un handler pour une querye
	 *
	 * @param IQuery $query Query dont on cherche le handler
	 * @return IQueryHandler[]
	 * @throws NoQueryHandlerFound
	 */
	public function resolveQueryHandlers(IQuery $query):array;
}