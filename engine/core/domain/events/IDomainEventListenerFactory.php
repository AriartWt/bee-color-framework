<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/02/18
 * Time: 04:54
 */

namespace wfw\engine\core\domain\events;

/**
 * Factory de DomainEventiLstener
 */
interface IDomainEventListenerFactory
{
    /**
     * @param string $listenerClass Listener à créer
     * @param array  $params Paramètres de création
     * @return IDomainEventListener
     */
    public function build(string $listenerClass,array $params=[]):IDomainEventListener;
}