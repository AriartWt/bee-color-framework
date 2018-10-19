<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/11/17
 * Time: 02:52
 */

namespace wfw\engine\core\domain\events;

/**
 *  Dispatche un événement ou un groupe d'événements
 */
interface IDomainEventDispatcher
{
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