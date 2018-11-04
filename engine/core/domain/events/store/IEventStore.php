<?php
namespace wfw\engine\core\domain\events\store;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\aggregate\IAggregateRoot;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Base de l'eventStore
 */
interface IEventStore {
	/**
	 *  Retourne un aggrégat grace à son UUID
	 *
	 * @param UUID $aggregateId Identifiant de l'aggrégat
	 *
	 * @return IAggregateRoot|null
	 */
	public function get(UUID $aggregateId):?IAggregateRoot;
	
	/**
	 * Retourne tous les aggrégats correspondant aux identifiants.
	 * @param UUID[] $aggregatesId Liste des identifiants d'aggrégats
	 * @return IAggregateRoot[]
	 */
	public function getAll(UUID... $aggregatesId):array;

	/**
	 *  Enregistre une séquence d'événements pour un aggrégat
	 *
	 * @param IAggregateRoot $aggregate Aggregat concerné par les événements
	 * @param ICommand $command (optionnel) Commande à l'origine de la mise à jour de
	 *                          l'aggrégat
	 *
	 */
	public function save(IAggregateRoot $aggregate, ?ICommand $command = null);
	
	/**
	 * Enregistre les séquences d'événements de tous les AggregateRoot
	 * @param null|ICommand  $command Commande à l'origine de la mise à jour des aggrégats
	 * @param IAggregateRoot ...$aggregates Liste des aggrégats
	 */
	public function saveAll(?ICommand $command = null, IAggregateRoot... $aggregates);
}