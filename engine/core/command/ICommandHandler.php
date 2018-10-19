<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 06/11/17
 * Time: 03:29
 */

namespace wfw\engine\core\command;

/**
 *  Permet de traiter une commande
 */
interface ICommandHandler
{
    /**
     * Traite la commande
     * @param ICommand $command Commande à traiter
     */
    public function handle(ICommand $command);
}