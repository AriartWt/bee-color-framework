<?php
namespace wfw\engine\core\domain\events\observers;

use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventListenerFactory;
use wfw\engine\core\domain\events\IDomainEventObserver;

/**
 * Utilise un tableau de configurations pour définir les listeners qui écoutent.
 * Ils sont alors instancies la première fois qu'ils sont invoqués, puis enregistrés dans
 * l'observer.
 */
final class ConfBasedDomainEventObserver implements IDomainEventObserver {
	/** @var IDomainEventListenerFactory $_listenerFactory */
	private $_listenerFactory;
	/** @var array $_listeners */
	private $_listeners;
	/** @var IDomainEventObserver $_observer */
	private $_observer;

	/**
	 * Attention : si un listener est définit dans $listeners et déjà enregistré pour un événement
	 * donné dans l'observeur, le listener sera ignoré, et celui de l'observer sera conservé.
	 *
	 * @param string[][] $listeners Liste des listeners
	 *                              (DomainEvent::class => DomainEventListener::class[])
	 * @param IDomainEventObserver        $observer Observer décoré par l'instance courante
	 * @param IDomainEventListenerFactory $factory  Factory pour la création des
	 *                                              DomainEventListeners
	 */
	public function __construct(
		array $listeners,
		IDomainEventObserver $observer,
		IDomainEventListenerFactory $factory
	) {
		$this->_listenerFactory = $factory;
		$this->_observer = $observer;
		$this->_listeners = [];

		foreach ($listeners as $eventClass => $listenerClasses){
			if(is_a($eventClass,IDomainEvent::class,true)){
				foreach($listenerClasses as $listenerClass => $params){
					if(!is_a($listenerClass,IDomainEventListener::class,true))
						throw new \InvalidArgumentException(
							"Invalid domain event listener class ".$listenerClass
							." does'nt implements ".IDomainEventListener::class
						);
				}

				$registeredListernerClasses = [];
				foreach($observer->getDomainEventListeners($eventClass) as $listener){
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
					.IDomainEvent::class
				);
			}
		}
	}

	/**
	 * Dispatche un événement
	 * @param IDomainEvent $e Evenement à dispatcher
	 */
	public function dispatchDomainEvent(IDomainEvent $e): void {
		$this->dispatchAllDomainEvents(new EventList([$e]));
	}

	/**
	 * @param EventList $events Evenements à dispatcher
	 */
	public function dispatchAllDomainEvents(EventList $events): void {
		//On cherche dans les listeners les listeners à créer
		if(count($this->_listeners) > 0){
			foreach($events as $e){
				$this->initListenersForEvent($e);
			}
		}
		$this->_observer->dispatchAllDomainEvents($events);
	}

	/**
	 * @param IDomainEvent $e Evenement pour lequel on cherche à initialiser les listeners
	 */
	private function initListenersForEvent(IDomainEvent $e){
		foreach($this->_listeners as $eventsClass=>$listeners){
			if($e instanceof $eventsClass){
				foreach($listeners as $listenerClass=>$params){
					$this->addDomainEventListener(
						$eventsClass,
						$this->_listenerFactory->buildDomainEventListener(
							$listenerClass,
							$params
						)
					);
				}
				unset($this->_listeners[$eventsClass]);
			}
		}
	}

	/**
	 * @param string $domainEventClass Evenement écouté
	 * @return IDomainEventListener[]
	 */
	public function getDomainEventListeners(string $domainEventClass): array {
		return $this->_observer->getDomainEventListeners($domainEventClass);
	}

	/**
	 * Ajoute un listener pour un événement métier
	 *
	 * @param string $domainEventClass Classe de l'événement à écouter.
	 *                                 Tiens compte de l'héritage
	 * @param IDomainEventListener $listener Listener à appeler
	 */
	public function addDomainEventListener(string $domainEventClass, IDomainEventListener $listener) {
		$this->_observer->addDomainEventListener($domainEventClass,$listener);
	}

	/**
	 * Supprime un ou plusieurs lsitener attachés à un événement
	 *
	 * @param string $domainEventClass Classe d'événement dont on souhaite supprimer
	 *                                 les listener
	 * @param null|IDomainEventListener $listener (optionnel) Listener à supprimer. Si null,
	 *                                            supprime tous les listeners de la classe
	 *                                            d'événement
	 */
	public function removeDomainEventListener(
		string $domainEventClass,
		?IDomainEventListener $listener = null
	) {
		$this->_observer->removeDomainEventListener(
			$domainEventClass,
			$listener
		);
	}
}