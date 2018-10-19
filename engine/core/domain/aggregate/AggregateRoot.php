<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/11/17
 * Time: 02:04
 */

namespace wfw\engine\core\domain\aggregate;

use wfw\engine\core\domain\aggregate\errors\NoHandlerForEvent;
use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\lib\PHP\errors\IllegalInvocation;
use wfw\engine\lib\PHP\objects\PHPClassName;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Représente une entité de base. (Ou Aggregate)
 */
abstract class AggregateRoot implements IAggregateRoot {
	/** @var UUID $_id */
	private $_id;
	/** @var EventList $_eventList */
	private $_eventList;
	/** @var int $_version */
	private $_version;

	/**
	 *  Constructeur
	 *
	 * @param IAggregateRootGeneratedEvent $e Evenement de création de l'aggrégat
	 */
	public function __construct(IAggregateRootGeneratedEvent $e){
		$this->_eventList = new EventList();
		$this->_id = $e->getAggregateId();
		$this->_version = -1;
		$this->registerEvent($e);
	}

	public final function __wakeup() {
		$this->_eventList = new EventList();
	}

	/**
	 *  Retourne l'identifiant de l'entité
	 * @return UUID
	 */
	public final function getId():UUID{
		return $this->_id;
	}

	/**
	 *  Events appliqués pour retrovuer l'état de l'aggregat
	 *
	 * @param IDomainEvent $event
	 *
	 * @throws NoHandlerForEvent
	 */
	public function apply(IDomainEvent $event) {
		$className = new PHPClassName(get_class($event));
		$methodName = "apply".$className->getName();
		if(method_exists($this,$methodName)){
			$this->$methodName($event);
			$this->_version++;
		}else{
			throw new NoHandlerForEvent("No $methodName exists to handle this event !");
		}
	}

	/**
	 *  Renvoie une copie de la liste des événements créés par l'aggrégat courant
	 * @return EventList
	 */
	public final function getEventList(): EventList{
		return new EventList($this->_eventList->toArray());
	}

	/**
	 *  Enregistre un nouvel événement créé par l'aggrégat
	 *
	 * @param IDomainEvent $e Evenement à ajouter
	 */
	protected final function registerEvent(IDomainEvent $e){
		$this->_eventList->add($e);
		$this->apply($e);
	}

	public final function resetEventList(){ $this->_eventList = new EventList(); }

	/**
	 *  Retourne la version de l'aggrégat
	 * @return int
	 */
	public final function getVersion():int { return $this->_version; }

	/**
	 *  Retourne la version de l'aggrégat avant l'application des nouveaux événements
	 * @return int
	 */
	public final function getVersionBeforeEvents(): int {
		return $this->_version - $this->_eventList->getLength();
	}

	/**
	 *  Par défaut deux aggrégats sont identiques si leur identifiant est identique
	 *
	 * @param IAggregateRoot $agg
	 *
	 * @return bool
	 */
	public function equals(IAggregateRoot $agg){ return $this->_id->equals($agg->getId()); }

	/**
	 * Recrée un aggregat à partir de son événement
	 * @param IAggregateRootGeneratedEvent $e
	 * @return IAggregateRoot
	 */
	public final static function restoreAggregateFromEvent(IAggregateRootGeneratedEvent $e):IAggregateRoot{
		if(self::class === static::class)
			throw new IllegalInvocation("Can't restore an aggregate from abstract aggregate ");
		$ag = new static(...$e->getConstructorArgs());
		$ag->resetEventList();
		$ag->apply($e);
		return $ag;
	}
}