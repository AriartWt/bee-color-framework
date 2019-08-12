<?php
namespace wfw\engine\core\query;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory basée sur dice pour la création d'un command handler
 */
final class QueryHandlerFactory implements IQueryHandlerFactory {
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
	 * Constuit un IQueryHandler à partir du nom de la classe d'un CommandHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return IQueryHandler
	 */
	public function buildCommandHandler(string $handlerClass, array $params=[]): IQueryHandler {
		return $this->_factory->create($handlerClass,$params,[IQueryHandler::class]);
	}
}