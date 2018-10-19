<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 06/11/17
 * Time: 03:32
 */

namespace wfw\engine\core\command;

/**
 *  Traite les commandes de manières synchrone
 */
final class SynchroneCommandBus implements ICommandBus
{
    /**
     *  Inflecteur de commande (permet de savoir quel handler correspond à quelle commande)
     * @var ICommandInflector $_inflector
     */
    private $_inflector;

    /**
     *  SynchroneCommandBus constructor.
     *
     * @param ICommandInflector $inflector Trouve le handler d'une commande
     */
    public function __construct(ICommandInflector $inflector)
    {
        $this->_inflector = $inflector;
    }

    /**
     * Redirige la commande vers son handler et retourne le résultat du handler
     * @param ICommand $command Commande à rediriger
     */
    public function execute(ICommand $command):void
    {
        $handlers = $this->_inflector->resolveHandlers($command);
        foreach($handlers as $handler){
            $handler->handle($command);
        }
    }
}