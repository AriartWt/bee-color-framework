<?php
namespace wfw\engine\core\query;

/**
 * Factory de IQueryHandler
 */
interface IQueryHandlerFactory {
	/**
	 * Constuit un IQueryHandler à partir du nom de la classe d'un QueryHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return IQueryHandler
	 */
	public function buildQueryHandler(string $handlerClass, array $params=[]):IQueryHandler;
}