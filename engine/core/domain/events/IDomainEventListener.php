<?php
namespace wfw\engine\core\domain\events;

/**
 *  Ecouteur d'événements métier
 */
interface IDomainEventListener {
	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e):void;
}