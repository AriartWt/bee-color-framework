<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\modelManager;

use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\observers\DomainEventObserver;

/**
 * Modifie le comportement de la classe DomainEventManager de façon à enregistrer les listeners
 * impactés par la propagation d'un événement.
 */
final class MMDomainEventManager extends DomainEventObserver {
	/** @var IDomainEventListener[] */
	private $_impactedListeners=[];

	/**
	 * @param IDomainEvent $e
	 */
	public function dispatchDomainEvent(IDomainEvent $e): void {
		parent::dispatchDomainEvent($e);
		foreach($this->getListeners() as $listened=>$listeners){
			if($e instanceof $listened){
				foreach($listeners as $listener){
					$this->_impactedListeners[get_class($listener)]=[
						"model" => $listener,
						"date" => microtime(true)
					];
				}
			}
		}
	}

	/**
	 * @return IDomainEventListener[] Liste des listeners impactés depuis la création de la classe
	 *                                        ou le dernier appel à reset().
	 *                                        Le tableau est sous la forme : class => ["date"=>microtime(true),"model"=>IModel]
	 */
	public function getImpactedListeners():array{
		return $this->_impactedListeners;
	}

	/**
	 * Remet à zéro la liste des écouteurs impacté par l'événement
	 *
	 * @param array $models Liste des dmodels sous la forme "class" => microtime(true) date.
	 */
	public function reset(array $models):void{
		foreach($models as $class=>$time){
			if(isset($this->_impactedListeners[$class]) && $this->_impactedListeners[$class]["date"] <= $time){
				unset($this->_impactedListeners[$class]);
			}
		}
	}
}