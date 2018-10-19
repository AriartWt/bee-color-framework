<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/02/18
 * Time: 08:24
 */

namespace wfw\engine\core\command;

/**
 * Utilise un inflecteur pour déterminer le command handler à charger, évite les configurations
 * manuelles.
 */
final class InflectorBasedCommandDispatcher implements ICommandDispatcher
{
    /**
     * @var ICommandInflector $_inflector
     */
    private $_inflector;

    /**
     * InflectorBasedCommandObserver constructor.
     *
     * @param ICommandInflector $inflector Inflecteur
     */
    public function __construct(ICommandInflector $inflector)
    {
        $this->_inflector = $inflector;
    }

    /**
     * @param ICommand $command Commande à dispatcher
     * @throws NoHandlerFound
     */
    public function dispatch(ICommand $command): void
    {
        $handlers = $this->_inflector->resolveHandlers($command);
        foreach($handlers as $handler){
            $handler->handle($command);
        }
    }
}