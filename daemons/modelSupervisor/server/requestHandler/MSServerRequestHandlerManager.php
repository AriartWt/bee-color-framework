<?php
namespace wfw\daemons\modelSupervisor\server\requestHandler;

use wfw\daemons\modelSupervisor\server\IMSServerQuery;
use wfw\daemons\modelSupervisor\server\IMSServerRequest;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 *  Permet de mettre en place des écouteurs sur des requête client
 */
class MSServerRequestHandlerManager implements IMSServerRequestHandlerManager {
	/** @var IMSServerRequestHandler[][] $_listeners */
	private $_listeners = [];

	/**
	 *  Dispatche une requête
	 *
	 * @param IMSServerQuery $request Requête à dispatcher
	 *
	 * @return int Nombre de handlers appelés
	 */
	public function dispatch(IMSServerQuery $request):int {
		$hits=0;
		foreach($this->_listeners as $clientRequestClass=>$handlers){
			if(is_a($request->getInternalRequest()->getRequestClass(),$clientRequestClass,true)){
				foreach($handlers as $handler){
					$hits++;
					$handler->handleModelManagerQuery($request);
				}
			}
		}
		return $hits;
	}

	/**
	 *  Ajoute un handler de requêtes
	 *
	 * @param string                          $clientRequestClass
	 * @param IMSServerRequestHandler $handler
	 */
	public function addRequestHandler(string $clientRequestClass, IMSServerRequestHandler $handler) {
		if(!is_a($clientRequestClass,IMSServerRequest::class,true)){
			throw new IllegalInvocation(
				"$clientRequestClass is not a valide class : class doesn't implements "
				.IMSServerRequest::class
			);
		}
		if(!isset($this->_listeners[$clientRequestClass])){
			$this->_listeners[$clientRequestClass]=[];
		}
		$this->_listeners[$clientRequestClass][]=$handler;
	}
}