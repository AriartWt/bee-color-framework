<?php
namespace wfw\engine\core\command;

/**
 * Factory de ICommandHandler
 */
interface ICommandHandlerFactory {
	/**
	 * Constuit un ICommandHandler à partir du nom de la classe d'un CommandHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return ICommandHandler
	 */
	public function buildCommandHandler(string $handlerClass, array $params=[]):ICommandHandler;
}