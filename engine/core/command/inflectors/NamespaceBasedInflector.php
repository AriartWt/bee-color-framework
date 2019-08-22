<?php
namespace wfw\engine\core\command\inflectors;

use wfw\engine\core\command\errors\NoCommandHandlerFound;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandHandler;
use wfw\engine\core\command\ICommandHandlerFactory;
use wfw\engine\core\command\ICommandInflector;

/**
 * Tente de résoudre les CommandHandlers en se basant sur le namespace de la commande.
 * Exemple : la commande \wfw\engine\packages\users\commands\CreateUser
 *           sera résolue : \wfw\engine\packages\users\commands\handlers\CreateUserHandler
 */
final class NamespaceBasedInflector implements ICommandInflector {
	/** @var ICommandHandlerFactory $_factory */
	private $_factory;
	/** @var ICommandHandler[][] $_handlers */
	private $_handlers;
	/** @var ICommandHandler $_resolved */
	private $_resolved;

	/**
	 * NamespaceBasedInflector constructor.
	 *
	 * @param ICommandHandlerFactory $factory Factory de handlers
	 * @param array                  $handlers
	 */
	public function __construct(ICommandHandlerFactory $factory, array $handlers = []) {
		$this->_factory = $factory;
		$this->_handlers = [];
		$this->_resolved = [];
		foreach ($handlers as $commandClass => $handlerClasses){
			if(!is_a($commandClass,ICommand::class,true))
				throw new \InvalidArgumentException(
					"$commandClass doesn't implements ".ICommand::class
				);
			if(!isset($this->_handlers[$commandClass])) $this->_handlers[$commandClass] = [];
			foreach($handlerClasses as $class=>$params){
				if(!is_a($class,ICommandHandler::class,true))
					throw new \InvalidArgumentException(
						"$class doesn't implements ".ICommandHandler::class
					);
				$this->_handlers[$commandClass][$class] = $factory->buildCommandHandler(
					$class,$params
				);
			}
		}
	}

	/**
	 *  Trouve un handler pour une commande
	 *
	 * @param ICommand $command Comande dont on cherche le handler
	 * @return ICommandHandler[]
	 * @throws NoCommandHandlerFound
	 */
	public function resolveCommandHandlers(ICommand $command): array {
		$handlers = $this->resolveHandlersFromCommandClass(get_class($command));
		if(count($handlers)>0){
			return $handlers;
		}else{
			throw new NoCommandHandlerFound(
				"No command handler found for command ".get_class($command)
			);
		}
	}

	/**
	 * @param string $command Classe de la commande à résoudre.
	 * @return array
	 */
	private function resolveHandlersFromCommandClass(string $command): array {
		$res = [];
		if(isset($this->_resolved[$command])){
			$res[] = $this->_handlers[$command];
		}else{
			$r = [];
			if (($pos = strrpos($command, $search = "\\command\\")) !== false) {
				$handlerClass = substr_replace(
					$command,
					"\\command\\handlers\\",
					$pos,
					strlen($search)
				);
			}
			try{
				$r[] = $this->_factory->buildCommandHandler(
					($handlerClass ?? $command)."Handler"
				);
			}catch(\Exception $e){}

			$this->_handlers[$command] = array_merge(
				$this->_handlers[$command] ?? [],
				$r
			);
			$res[] = $r;
		}
		foreach($this->_handlers as $class=>$handlers){
			if(is_a($command,$class)) $res[] = $handlers;
		}
		return  array_merge(...$res);
	}
}