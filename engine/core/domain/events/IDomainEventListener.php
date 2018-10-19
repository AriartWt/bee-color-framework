<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/12/17
 * Time: 00:40
 */

namespace wfw\engine\core\domain\events;

/**
 *  Ecouteur d'événements métier
 */
interface IDomainEventListener
{
    /**
     * Méthode appelée lors de la reception d'un événement
     * @param IDomainEvent $e Evenement reçu
     */
    public function recieveEvent(IDomainEvent $e):void;
}