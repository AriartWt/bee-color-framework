<?php
namespace wfw\daemons\modelSupervisor\server\requestHandler;

use wfw\daemons\modelSupervisor\server\IMSServerQuery;

/**
 *  Gestionnaire de requêtes clientes
 */
interface IMSServerRequestHandlerManager {
	/**
	 *  Dispatche une requête.
	 *
	 * @param IMSServerQuery $request Requête à dispatcher
	 *
	 * @return int Nombre de handlers appelés
	 */
	public function dispatch(IMSServerQuery $request):int;

	/**
	 *  Ajoute un handler de requêtes
	 *
	 * @param string                          $clientRequestClass
	 * @param IMSServerRequestHandler $handler
	 */
	public function addRequestHandler(string $clientRequestClass, IMSServerRequestHandler $handler);
}