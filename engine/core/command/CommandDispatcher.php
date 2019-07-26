<?php
namespace wfw\engine\core\command;

use wfw\engine\core\command\errors\NoCommandHandlerFound;

/**
 * Dispatcher de commande base. Peut-être construit avec une liste de dispatchers de commandes.
 * L'instance courante tentera un à un chaque dispatcher dans l'ordre à chaque echec, et s'arrêtera
 * dés lors qu'un dispatch() aura réussi.
 */
final class CommandDispatcher implements ICommandDispatcher {
	/** @var ICommandDispatcher[] $_dispatchers */
	private $_dispatchers;

	/**
	 * CommandDispatcher constructor.
	 *
	 * @param ICommandDispatcher ...$dispatchers
	 */
	public function __construct(ICommandDispatcher ...$dispatchers) {
		$this->_dispatchers = $dispatchers;
	}

	/**
	 * @param ICommand $command Commande à dispatcher
	 * @throws NoCommandHandlerFound
	 */
	public function dispatchCommand(ICommand $command): void {
		$success = false;
		foreach ($this->_dispatchers as $dispatcher){
			try{
				$dispatcher->dispatchCommand($command);
				$success = true;
				break;
			}catch(NoCommandHandlerFound $e){}
		}
		if(!$success){
			throw new NoCommandHandlerFound("No handler found for command ".get_class($command));
		}
	}
}