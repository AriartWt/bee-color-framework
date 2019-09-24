<?php
namespace wfw\engine\core\domain\events;

/**
 * Un aggregat a été généré.
 */
interface IAggregateRootGeneratedEvent extends IDomainEvent {
	/**
	 * @return array Arguments du constructeur de l'aggrégat
	 */
	public function getConstructorArgs():array;
}