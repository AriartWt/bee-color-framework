<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\errors\NoQueryHandlerFound;

/**
 * Dispatcher de query base. Peut-être construit avec une liste de dispatchers de queries.
 * L'instance courante tentera un à un chaque dispatcher dans l'ordre à chaque echec, et s'arrêtera
 * dés lors qu'un dispatch() aura réussi.
 */
final class QueryDispatcher implements IQueryDispatcher {
	/** @var IQueryDispatcher[] $_dispatchers */
	private $_dispatchers;

	/**
	 * QueryDispatcher constructor.
	 *
	 * @param IQueryDispatcher ...$dispatchers
	 */
	public function __construct(IQueryDispatcher ...$dispatchers) {
		$this->_dispatchers = $dispatchers;
	}

	/**
	 * @param IQuery $query Querye à dispatcher
	 * @throws NoQueryHandlerFound
	 */
	public function dispatchQuery(IQuery $query): void {
		$success = false;
		foreach ($this->_dispatchers as $dispatcher){
			try{
				$dispatcher->dispatchQuery($query);
				$success = true;
				break;
			}catch(NoQueryHandlerFound $e){}
		}
		if(!$success){
			throw new NoQueryHandlerFound("No handler found for query ".get_class($query));
		}
	}
}