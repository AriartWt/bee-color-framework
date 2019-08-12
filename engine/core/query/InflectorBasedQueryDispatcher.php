<?php
namespace wfw\engine\core\query;

/**
 * Utilise un inflecteur pour déterminer le query handler à charger, évite les configurations
 * manuelles.
 */
final class InflectorBasedQueryDispatcher implements IQueryDispatcher {
	/** @var IQueryInflector $_inflector */
	private $_inflector;

	/**
	 * InflectorBasedQueryObserver constructor.
	 *
	 * @param IQueryInflector $inflector Inflecteur
	 */
	public function __construct(IQueryInflector $inflector) {
		$this->_inflector = $inflector;
	}

	/**
	 * @param IQuery $query Querye à dispatcher
	 * @throws NoHandlerFound
	 */
	public function dispatchQuery(IQuery $query): void {
		$handlers = $this->_inflector->resolveQueryHandlers($query);
		foreach($handlers as $handler){
			$handler->handleQuery($query);
		}
	}
}