<?php
namespace wfw\engine\core\domain\aggregate;

use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\EventList;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Aggrégat de base
 */
interface IAggregateRoot {
	/**
	 *  Retourne l'identifiant de l'aggrégat
	 * @return UUID
	 */
	public function getId():UUID;

	/**
	 *  Applique un événement à l'aggrégat
	 *
	 * @param IDomainEvent $event
	 */
	public function apply(IDomainEvent $event);

	/**
	 *  Retourne la liste des événements générés par l'aggrégat depuis son dernier chargement en mémoire. Ces événements doivent être persistés.
	 *
	 * @return \wfw\engine\core\domain\events\EventList
	 */
	public function getEventList():EventList;

	/**
	 *  Reset la liste des événements de l'aggrégat (après persistence par exemple) A pour effet de fixer la version de l'aggrégat à la version courante.
	 */
	public function resetEventList();

	/**
	 *  Retourne la version courante de l'aggrégat
	 * @return int
	 */
	public function getVersion():int;

	/**
	 *  Retourne la version de l'aggrégat à sa dernière persistence.
	 * @return int
	 */
	public function getVersionBeforeEvents():int;

	/**
	 * @param IAggregateRootGeneratedEvent $e
	 * @return IAggregateRoot
	 */
	public static function restoreAggregateFromEvent(IAggregateRootGeneratedEvent $e):IAggregateRoot;
}