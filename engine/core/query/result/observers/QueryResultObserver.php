<?php
namespace wfw\engine\core\query\result\observers;

use wfw\engine\core\query\result\IQueryResult;
use wfw\engine\core\query\result\IQueryResultListener;
use wfw\engine\core\query\result\IQueryResultObserver;

/**
 *  Gestionnaire d'événements métier
 */
class QueryResultObserver implements IQueryResultObserver {
	/** @var IQueryResultListener[][] */
	private $_listeners=[];

	/**
	 * @return IQueryResultListener[]
	 */
	protected function getListeners():array{
		return $this->_listeners;
	}

	/**
	 *  Dispatche un événement
	 *
	 * @param IQueryResult $e Evenement à dispatcher
	 */
	public function dispatchQueryResult(IQueryResult $e): void {
		foreach($this->_listeners as $listenedEvent=>$listeners){
			if($e instanceof $listenedEvent){
				foreach($listeners as $listener){
					/** @var IQueryResultListener $listener */
					$listener->recieveQueryResult($e);
				}
			}
		}
	}

	/**
	 *  Ajoute un listener pour un événement métier
	 *
	 * @param string               $domainEventClass Classe de l'événement à écouter. Tiens compte de l'héritage
	 * @param IQueryResultListener $listener         Listener à appeler
	 */
	public function addQueryResultListener(string $domainEventClass, IQueryResultListener $listener):void{
		if(is_a($domainEventClass,IQueryResult::class,true)){
			if(!isset($this->_listeners[$domainEventClass])){
				$this->_listeners[$domainEventClass] = [];
			}
			$this->_listeners[$domainEventClass][] = $listener;
		}else{
			throw new \InvalidArgumentException("Class $domainEventClass have to implements ".IQueryResult::class);
		}
	}

	/**
	 *  Supprime un ou plusieurs lsitener attachés à un événement
	 *
	 * @param string $domainEventClass Classe d'événement dont on souhaite supprimer les listener
	 * @param null|IQueryResultListener $listener (optionnel) Listener à supprimer. Si null, supprime tous les lsitener de la classe d'événement
	 */
	public function removeQueryResultListener(
		string $domainEventClass,
		?IQueryResultListener $listener = null
	):void {
		if(isset($this->_listeners[$domainEventClass])){
			if(!is_null($listener)){
				$offset = array_search($listener,$this->_listeners[$domainEventClass]);
				if(!is_bool($offset)){
					array_splice($this->_listeners[$domainEventClass],$offset,1);
				}
			}else{
				unset($this->_listeners[$domainEventClass]);
			}
		}
	}

	/**
	 * Supprime tous les listeners qui sont de sinstances de $listenerClass
	 * @param string $listenerClass Classe du listener à supprimer
	 */
	public function removeEventListenerByClassName(string $listenerClass){
		foreach($this->_listeners as $domainEventClass=>$listeners){
			foreach($listeners as $k=>$listener){
				if($listener instanceof $listenerClass){
					array_splice($this->_listeners[$domainEventClass],$k,1);
				}
			}
		}
	}

	/**
	 * @param IQueryResult[] $queryResults Evenemnts à dispatcher
	 */
	public function dispatchAllQueryResults(IQueryResult... $queryResults): void {
		foreach($queryResults as $e){
			$this->dispatchQueryResult($e);
		}
	}

	/**
	 * @param string $class Classe de l'événement dont on souhaite récupérer les listeners
	 * @return IQueryResultListener[]
	 */
	public function getQueryResultListeners(string $class): array {
		$res = [];
		foreach($this->_listeners as $domainEventClass=>$listeners){
			if(is_a($domainEventClass,$class,true)){
				$res = array_merge($res,$this->_listeners[$domainEventClass]);
			}
		}
		return $res;
	}
}