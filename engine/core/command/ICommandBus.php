<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 06/11/17
 * Time: 03:30
 */

namespace wfw\engine\core\command;

/**
 *  Reçois les commandes et les redirige vers leur handler
 */
interface ICommandBus
{
    /**
     *  Redirige la commande vers son handler
     *
     * @param ICommand $command Commande à rediriger
     *
     * @return mixed
     */
    public function execute(ICommand $command):void;
}