<?php
namespace wfw\engine\core\query;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory basée sur dice pour la création d'un query handler
 */
final class QueryHandlerFactory implements IQueryHandlerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * DiceBasedQueryHandlerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Constuit un IQueryHandler à partir du nom de la classe d'un QueryHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return IQueryHandler
	 */
	public function buildQueryHandler(string $handlerClass, array $params=[]): IQueryHandler {
		return $this->_factory->create($handlerClass,$params,[IQueryHandler::class]);
	}
}