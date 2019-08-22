<?php
namespace wfw\engine\core\query\result\observers;

use wfw\engine\core\query\result\IQueryResult;
use wfw\engine\core\query\result\IQueryResultListener;
use wfw\engine\core\query\result\IQueryResultListenerFactory;
use wfw\engine\core\query\result\IQueryResultObserver;

/**
 * Utilise un tableau de configurations pour définir les listeners qui écoutent.
 * Ils sont alors instancies la première fois qu'ils sont invoqués, puis enregistrés dans
 * l'observer.
 */
final class ConfBasedQueryResultObserver implements IQueryResultObserver {
	/** @var IQueryResultListenerFactory $_listenerFactory */
	private $_listenerFactory;
	/** @var array $_listeners */
	private $_listeners;
	/** @var IQueryResultObserver $_observer */
	private $_observer;

	/**
	 * Attention : si un listener est définit dans $listeners et déjà enregistré pour un événement
	 * donné dans l'observeur, le listener sera ignoré, et celui de l'observer sera conservé.
	 *
	 * @param string[][] $listeners Liste des listeners
	 *                              (QueryResult::class => QueryResultListener::class[])
	 * @param IQueryResultObserver        $observer Observer décoré par l'instance courante
	 * @param IQueryResultListenerFactory $factory  Factory pour la création des
	 *                                              QueryResultListeners
	 */
	public function __construct(
		array $listeners,
		IQueryResultObserver $observer,
		IQueryResultListenerFactory $factory
	) {
		$this->_listenerFactory = $factory;
		$this->_observer = $observer;
		$this->_listeners = [];

		foreach ($listeners as $eventClass => $listenerClasses){
			if(is_a($eventClass,IQueryResult::class,true)){
				foreach($listenerClasses as $listenerClass => $params){
					if(!is_a($listenerClass,IQueryResultListener::class,true))
						throw new \InvalidArgumentException(
							"Invalid domain event listener class ".$listenerClass
							." does'nt implements ".IQueryResultListener::class
						);
				}

				$registeredListernerClasses = [];
				foreach($observer->getQueryResultListeners($eventClass) as $listener){
					$registeredListernerClasses[get_class($listener)] = true;
				}
				if(!isset($this->_listeners[$eventClass])) $this->_listeners[$eventClass] = [];
				foreach($listenerClasses as $class=>$params){
					if(!isset($registeredListernerClasses[$class]))
						$this->_listeners[$eventClass][$class] = $params;
				}
			}else{
				throw new \InvalidArgumentException(
					"Invalid domain event class : $eventClass doesn't implements "
					.IQueryResult::class
				);
			}
		}
	}

	/**
	 * Dispatche un événement
	 * @param IQueryResult $e Evenement à dispatcher
	 */
	public function dispatchQueryResult(IQueryResult $e): void {
		$this->dispatchAllQueryResults($e);
	}

	/**
	 * @param IQueryResult[] $queryResults QueryResult to dispatch
	 */
	public function dispatchAllQueryResults(IQueryResult... $queryResults): void {
		//On cherche dans les listeners les listeners à créer
		if(count($this->_listeners) > 0){
			foreach($queryResults as $e){
				$this->initListenersForEvent($e);
			}
		}
		$this->_observer->dispatchAllQueryResults($queryResults);
	}

	/**
	 * @param IQueryResult $e Evenement pour lequel on cherche à initialiser les listeners
	 */
	private function initListenersForEvent(IQueryResult $e){
		foreach($this->_listeners as $queryResultsClass=>$listeners){
			if($e instanceof $queryResultsClass){
				foreach($listeners as $listenerClass=>$params){
					$this->addQueryResultListener(
						$queryResultsClass,
						$this->_listenerFactory->buildQueryResultListener(
							$listenerClass,
							$params
						)
					);
				}
				unset($this->_listeners[$queryResultsClass]);
			}
		}
	}

	/**
	 * @param string $domainEventClass Evenement écouté
	 * @return IQueryResultListener[]
	 */
	public function getQueryResultListeners(string $domainEventClass): array {
		return $this->_observer->getQueryResultListeners($domainEventClass);
	}

	/**
	 * Ajoute un listener pour un événement métier
	 *
	 * @param string $domainEventClass Classe de l'événement à écouter.
	 *                                 Tiens compte de l'héritage
	 * @param IQueryResultListener $listener Listener à appeler
	 */
	public function addQueryResultListener(string $domainEventClass, IQueryResultListener $listener) {
		$this->_observer->addQueryResultListener($domainEventClass,$listener);
	}

	/**
	 * Supprime un ou plusieurs lsitener attachés à un événement
	 *
	 * @param string $domainEventClass Classe d'événement dont on souhaite supprimer
	 *                                 les listener
	 * @param null|IQueryResultListener $listener (optionnel) Listener à supprimer. Si null,
	 *                                            supprime tous les listeners de la classe
	 *                                            d'événement
	 */
	public function removeQueryResultListener(
		string $domainEventClass,
		?IQueryResultListener $listener = null
	) {
		$this->_observer->removeQueryResultListener(
			$domainEventClass,
			$listener
		);
	}
}