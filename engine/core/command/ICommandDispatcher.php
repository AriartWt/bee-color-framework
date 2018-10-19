<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/02/18
 * Time: 11:40
 */

namespace wfw\engine\core\command;

use wfw\engine\core\command\errors\NoCommandHandlerFound;

/**
 * Dispatcher de commandes
 */
interface ICommandDispatcher
{
    /**
     * @param ICommand $command Commande à dispatcher
     * @throws NoCommandHandlerFound
     */
    public function dispatch(ICommand $command):void;
}