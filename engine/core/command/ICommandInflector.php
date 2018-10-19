<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 06/11/17
 * Time: 03:34
 */

namespace wfw\engine\core\command;

use wfw\engine\core\command\errors\NoCommandHandlerFound;

/**
 *  Permet de trouver un handler pour une commande
 */
interface ICommandInflector
{
    /**
     *  Trouve un handler pour une commande
     *
     * @param ICommand $command Comande dont on cherche le handler
     * @return ICommandHandler[]
     * @throws NoCommandHandlerFound
     */
    public function resolveHandlers(ICommand $command):array;
}