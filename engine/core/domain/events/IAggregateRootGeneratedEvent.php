<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/04/18
 * Time: 08:31
 */

namespace wfw\engine\core\domain\events;

/**
 * Un aggregat a été généré.
 */
interface IAggregateRootGeneratedEvent extends IDomainEvent
{
    /**
     * @return array Arguments du constructeur de l'aggrégat
     */
    public function getConstructorArgs():array;
}