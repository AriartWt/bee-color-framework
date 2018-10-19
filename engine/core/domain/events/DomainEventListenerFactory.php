<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 14:00
 */

namespace wfw\engine\core\domain\events;

use Dice\Dice;

/**
 * Factroy de DomainEventListener basée sur Dice
 */
final class DomainEventListenerFactory implements IDomainEventListenerFactory
{
    /** @var Dice $_dice */
    private $_dice;

    /**
     * DomainEventListenerFactory constructor.
     *
     * @param Dice $dice
     */
    public function __construct(Dice $dice) {
        $this->_dice = $dice;
    }

    /**
     * @param string $listenerClass Listener à créer
     * @param array  $params Paramètres de création
     * @return IDomainEventListener
     */
    public function build(string $listenerClass,array $params=[]): IDomainEventListener
    {
        /** @var IDomainEventListener $factory */
        $factory = $this->_dice->create($listenerClass,$params);
        return $factory;
    }
}