<?php
namespace wfw\engine\core\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\aggregate\IAggregateRoot;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Repository d'aggregate root
 */
interface IAggregateRootRepository {
	/**
	 *  Ajoute un AggregateRoot au repository.
	 *
	 * @param IAggregateRoot $aggregate Entité à ajouter
	 * @param null|ICommand  $command   Commande ayant entrainée l'ajout
	 */
	public function addAggregateRoot(IAggregateRoot $aggregate, ICommand $command=null):void;
	
	/**
	 *  Ajoute un AggregateRoot au repository
	 *
	 * @param null|ICommand    $command    Commande ayant entrainée l'ajout
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à ajouter
	 */
	public function addAllAggregateRoots(?ICommand $command=null, IAggregateRoot... $aggregates):void;
	
	/**
	 * Modifie un AggregateRoot dans le repository.
	 *
	 * @param IAggregateRoot $aggregate Entité à modifier
	 * @param ICommand|null  $command Commande ayant entrainée la modification
	 */
	public function modifyAggregateRoot(IAggregateRoot $aggregate, ICommand $command=null):void;
	
	/**
	 * Modifie des AggregateRoot dans le repository.
	 *
	 * @param ICommand|null    $command    Commande ayant entrainée la modification
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à modifier
	 */
	public function modifyAllAggregateRoots(?ICommand $command=null, IAggregateRoot... $aggregates):void;
	
	/**
	 *  Supprime un AggregateRoot du repository.
	 *  Attention, un événement de suppression doit avoir été émis par l'aggrégat !
	 *
	 * @param IAggregateRoot $aggregate Entité à supprimer
	 * @param ICommand|null  $command Commande ayant entrainée la suppression
	 */
	public function removeAggregateRoot(IAggregateRoot $aggregate, ICommand $command=null):void;
	
	/**
	 *  Supprime plusieurs AggregateRoot du repository.
	 *  Attention, un événement de suppression doit avoir été émis par chaque aggrégats
	 *
	 * @param ICommand|null    $command    Commande ayant entrainée la suppression
	 * @param IAggregateRoot[] $aggregates Liste des aggrégats à supprimer
	 */
	public function removeAllAggregateRoots(?ICommand $command=null, IAggregateRoot... $aggregates):void;

	/**
	 *  Retrouve un AggregateRoot d'après son identifiant
	 *
	 * @param UUID          $aggregateId Identifiant de l'aggrégat
	 * @return null|IAggregateRoot
	 */
	public function getAggregateRoot(UUID $aggregateId):?IAggregateRoot;
	
	/**
	 * Retrouve plusieurs AggregateRoot d'après leur identifiants
	 * @param UUID[] $aggregatesId Identifiant des aggrégats
	 * @return array
	 */
	public function getAllAggregateRoots(UUID... $aggregatesId):array;
}