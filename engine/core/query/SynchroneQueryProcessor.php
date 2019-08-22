<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\security\errors\RejectedQuery;
use wfw\engine\core\query\security\IQuerySecurityCenter;

/**
 *  Traite les queryes de manières synchrone
 */
final class SynchroneQueryProcessor implements IQueryProcessor {
	/** @var IQueryInflector $_inflector */
	private $_inflector;
	/** @var IQuerySecurityCenter $_security */
	private $_security;

	/**
	 *  SynchroneQueryBus constructor.
	 *
	 * @param IQueryInflector      $inflector Trouve le handler d'une querye
	 * @param IQuerySecurityCenter $security
	 */
	public function __construct(IQueryInflector $inflector, IQuerySecurityCenter $security) {
		$this->_inflector = $inflector;
		$this->_security = $security;
	}

	/**
	 * Redirige la querye vers son handler et retourne le résultat du handler
	 * @param IQuery $query Querye à rediriger
	 */
	public function processQuery(IQuery $query):void {
		if(!$this->_security->allowQuery($query)) throw new RejectedQuery(
			"Access denied : ".get_class($query)." rejected by the security center."
		);
		/** @var IQueryHandler[] $handlers */
		$handlers = $this->_inflector->resolveQueryHandlers($query);
		foreach($handlers as $handler){
			$handler->handleQuery($query);
		}
	}
}