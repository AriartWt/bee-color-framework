<?php
namespace wfw\engine\core\query;

use wfw\engine\core\query\security\errors\RejectedQuery;
use wfw\engine\core\query\security\IQuerySecurityCenter;

/**
 *  Traite les commandes de manières synchrone
 */
final class QueryProcessor implements IQueryProcessor {
	/** @var IQueryInflector $_inflector */
	private $_inflector;
	/** @var IQuerySecurityCenter $_security */
	private $_security;

	/**
	 *  SynchroneCommandBus constructor.
	 *
	 * @param IQueryInflector      $inflector Trouve le handler d'une commande
	 * @param IQuerySecurityCenter $security
	 */
	public function __construct(IQueryInflector $inflector, IQuerySecurityCenter $security) {
		$this->_inflector = $inflector;
		$this->_security = $security;
	}

	/**
	 * Redirige la commande vers son handler et retourne le résultat du handler
	 * @param ICommand $command Commande à rediriger
	 */
	public function executeCommand(ICommand $command):void {
		if(!$this->_security->allowCommand($command)) throw new RejectedQuery(
			"Access denied : ".get_class($command)." rejected by the security center."
		);
		$handlers = $this->_inflector->resolveCommandHandlers($command);
		foreach($handlers as $handler){
			$handler->handleCommand($command);
		}
	}
}