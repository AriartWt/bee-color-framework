<?php
namespace wfw\engine\core\domain\events\observers;

use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;

/**
 *  Gestionnaire d'événements métier
 */
class DomainEventObserver implements IDomainEventObserver {
	/** @var IDomainEventListener[][] */
	private $_listeners=[];

	/**
	 * @return IDomainEventListener[]
	 */
	protected function getListeners():array{
		return $this->_listeners;
	}

	/**
	 *  Dispatche un événement
	 *
	 * @param IDomainEvent $e Evenement à dispatcher
	 */
	public function dispatchDomainEvent(IDomainEvent $e): void {
		foreach($this->_listeners as $listenedEvent=>$listeners){
			if($e instanceof $listenedEvent){
				foreach($listeners as $listener){
					/** @var IDomainEventListener $listener */
					$listener->recieveDomainEvent($e);
				}
			}
		}
	}

	/**
	 *  Ajoute un listener pour un événement métier
	 *
	 * @param string               $domainEventClass Classe de l'événement à écouter. Tiens compte de l'héritage
	 * @param IDomainEventListener $listener         Listener à appeler
	 */
	public function addDomainEventListener(string $domainEventClass, IDomainEventListener $listener):void{
		if(is_a($domainEventClass,IDomainEvent::class,true)){
			if(!isset($this->_listeners[$domainEventClass])){
				$this->_listeners[$domainEventClass] = [];
			}
			$this->_listeners[$domainEventClass][] = $listener;
		}else{
			throw new \InvalidArgumentException("Class $domainEventClass have to implements ".IDomainEvent::class);
		}
	}

	/**
	 *  Supprime un ou plusieurs lsitener attachés à un événement
	 *
	 * @param string $domainEventClass Classe d'événement dont on souhaite supprimer les listener
	 * @param null|IDomainEventListener $listener (optionnel) Listener à supprimer. Si null, supprime tous les lsitener de la classe d'événement
	 */
	public function removeDomainEventListener(
		string $domainEventClass,
		?IDomainEventListener $listener = null
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
	 * @param EventList $events Evenemnts à dispatcher
	 */
	public function dispatchAllDomainEvents(EventList $events): void {
		foreach($events as $e){
			$this->dispatchDomainEvent($e);
		}
	}

	/**
	 * @param string $class Classe de l'événement dont on souhaite récupérer les listeners
	 * @return IDomainEventListener[]
	 */
	public function getDomainEventListeners(string $class): array {
		$res = [];
		foreach($this->_listeners as $domainEventClass=>$listeners){
			if(is_a($domainEventClass,$class,true)){
				$res = array_merge($res,$this->_listeners[$domainEventClass]);
			}
		}
		return $res;
	}
}