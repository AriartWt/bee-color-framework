<?php
namespace wfw\engine\core\domain\events;


use wfw\engine\lib\PHP\types\UUID;

/**
 *  Evenement de base
 */
interface IDomainEvent {
	/**
	 *  UUID de l'événement
	 * @return UUID
	 */
	public function getUUID():UUID;

	/**
	 *  UUID de l'aggrégat ayant généré l'événement
	 * @return UUID
	 */
	public function getAggregateId():UUID;

	/**
	 *  Date de création de l'événement
	 * @return float
	 */
	public function getGenerationDate():float;
}