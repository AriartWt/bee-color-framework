<?php
namespace wfw\engine\core\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\aggregate\IAggregateRoot;
use wfw\engine\core\domain\events\store\IEventStore;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Repository de base
 */
final class AggregateRootRepository implements IAggregateRootRepository {
	/** @var IEventStore $_eventStore */
	private $_eventStore;

	/**
	 * Repository constructor.
	 *
	 * @param IEventStore $eventStore
	 */
	public function __construct(IEventStore $eventStore){
		$this->_eventStore = $eventStore;
	}

	/**
	 *  Ajoute une entité au repository si elle vient d'être créée, enregistre les modifications sinon
	 *
	 * @param IAggregateRoot $entity Entité à ajouter
	 * @param ICommand|null  $command
	 */
	public function addAggregateRoot(IAggregateRoot $entity, ICommand $command=null):void{
		$this->_eventStore->saveAggregateRoot($entity, $command);
	}

	/**
	 *  Supprime une entité du repository. Attention, un événement de suppression doit avoir été émis par l'aggrégat !
	 *
	 * @param IAggregateRoot $aggregate Entité à supprimer
	 * @param ICommand|null  $command
	 */
	public function removeAggregateRoot(IAggregateRoot $aggregate, ICommand $command=null):void{
		$this->_eventStore->saveAggregateRoot($aggregate, $command);
	}

	/**
	 *  Retrouve un aggrégat d'après son identifiant
	 *
	 * @param UUID $aggregateId Identifiant de l'aggrégat
	 *
	 * @return null|IAggregateRoot
	 */
	public function getAggregateRoot(UUID $aggregateId):?IAggregateRoot{
		return $this->_eventStore->getAggregateRoot($aggregateId);
	}

	/**
	 * Modifie une entité dans le repository.
	 *
	 * @param IAggregateRoot $aggregate Entité à modifier
	 * @param ICommand|null  $command
	 */
	public function modifyAggregateRoot(IAggregateRoot $aggregate, ICommand $command=null): void {
		$this->_eventStore->saveAggregateRoot($aggregate, $command);
	}
	
	/**
	 * Retrouve plusieurs aggrégats d'après leur identifiants
	 *
	 * @param UUID[] $aggregatesId Identifiant des aggrégats
	 * @return array
	 */
	public function getAllAggregateRoots(UUID... $aggregatesId): array {
		return $this->_eventStore->getAllAggregateRoot(...$aggregatesId);
	}
	
	/**
	 *  Ajoute un AggregateRoot au repository
	 *
	 * @param null|ICommand    $command    Commande ayant entrainée l'ajout
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à ajouter
	 */
	public function addAllAggregateRoots(?ICommand $command = null, IAggregateRoot... $aggregates): void {
		$this->_eventStore->saveAllAggregateRoots($command,...$aggregates);
	}
	
	/**
	 * Modifie des AggregateRoot dans le repository.
	 *
	 * @param ICommand|null    $command    Commande ayant entrainée la modification
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à modifier
	 */
	public function modifyAllAggregateRoots(?ICommand $command = null, IAggregateRoot... $aggregates): void {
		$this->_eventStore->saveAllAggregateRoots($command,...$aggregates);
	}
	
	/**
	 *  Supprime plusieurs AggregateRoot du repository.
	 *  Attention, un événement de suppression doit avoir été émis par chaque aggrégats
	 *
	 * @param ICommand|null    $command    Commande ayant entrainée la suppression
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à supprimer
	 */
	public function removeAllAggregateRoots(?ICommand $command = null, IAggregateRoot... $aggregates): void {
		$this->_eventStore->saveAllAggregateRoots($command,...$aggregates);
	}
}