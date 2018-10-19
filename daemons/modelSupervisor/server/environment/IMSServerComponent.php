<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/01/18
 * Time: 13:27
 */

namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Permet de gérer les components gréffés au ModelManagerServer
 */
interface IMSServerComponent
{
    /**
     *  Appelé par le ModuleInitializer
     */
    public function start():void;

    /**
     *  Appelé par le MSServer juste avant qu'il ne quitte, si la fonction haveToBeShutdownGracefully renvoie true
     */
    public function shutdown():void;

    /**
     * @return string Nom du composant
     */
    public function getName():string;
}