<?php
namespace wfw\engine\core\command;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory basée sur dice pour la création d'un command handler
 */
final class CommandHandlerFactory implements ICommandHandlerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * DiceBasedCommandHandlerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Constuit un ICommandHandler à partir du nom de la classe d'un CommandHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return ICommandHandler
	 */
	public function buildCommandHandler(string $handlerClass, array $params=[]): ICommandHandler {
		return $this->_factory->create($handlerClass,$params,[ICommandHandler::class]);
	}
}