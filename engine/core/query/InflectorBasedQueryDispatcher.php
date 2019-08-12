<?php
namespace wfw\engine\core\query;

/**
 * Utilise un inflecteur pour déterminer le command handler à charger, évite les configurations
 * manuelles.
 */
final class InflectorBasedQueryDispatcher implements IQueryDispatcher {
	/** @var ICommandInflector $_inflector */
	private $_inflector;

	/**
	 * InflectorBasedCommandObserver constructor.
	 *
	 * @param ICommandInflector $inflector Inflecteur
	 */
	public function __construct(ICommandInflector $inflector) {
		$this->_inflector = $inflector;
	}

	/**
	 * @param ICommand $command Commande à dispatcher
	 * @throws NoHandlerFound
	 */
	public function dispatchCommand(ICommand $command): void {
		$handlers = $this->_inflector->resolveCommandHandlers($command);
		foreach($handlers as $handler){
			$handler->handleCommand($command);
		}
	}
}