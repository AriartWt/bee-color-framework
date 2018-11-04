<?php
namespace wfw\engine\core\domain\events;

/**
 *  Dispatche un événement ou un groupe d'événements
 */
interface IDomainEventDispatcher {
	/**
	 * Dispatche un événement
	 * @param IDomainEvent $e Evenement à dispatcher
	 */
	public function dispatch(IDomainEvent $e):void;

	/**
	 * @param EventList $events
	 */
	public function dispatchAll(EventList $events):void;
}