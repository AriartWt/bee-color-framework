<?php
namespace wfw\engine\core\command;

use wfw\engine\core\command\security\errors\RejectedCommand;
use wfw\engine\core\command\security\ICommandSecurityCenter;

/**
 *  Traite les commandes de manières synchrone
 */
final class SynchroneCommandBus implements ICommandBus {
	/** @var ICommandInflector $_inflector */
	private $_inflector;
	/** @var ICommandSecurityCenter $_security */
	private $_security;

	/**
	 *  SynchroneCommandBus constructor.
	 *
	 * @param ICommandInflector      $inflector Trouve le handler d'une commande
	 * @param ICommandSecurityCenter $security
	 */
	public function __construct(ICommandInflector $inflector, ICommandSecurityCenter $security) {
		$this->_inflector = $inflector;
		$this->_security = $security;
	}

	/**
	 * Redirige la commande vers son handler et retourne le résultat du handler
	 * @param ICommand $command Commande à rediriger
	 */
	public function executeCommand(ICommand $command):void {
		if(!$this->_security->allowCommand($command)) throw new RejectedCommand(
			"Access denied : ".get_class($command)." rejected by the security center."
		);
		$handlers = $this->_inflector->resolveCommandHandlers($command);
		foreach($handlers as $handler){
			$handler->handleCommand($command);
		}
	}
}