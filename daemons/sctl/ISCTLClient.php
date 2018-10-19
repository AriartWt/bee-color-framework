<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/04/18
 * Time: 12:28
 */

namespace wfw\daemons\sctl;

/**
 * Client du daemon sctl
 */
interface ISCTLClient
{
    /**
     * Ordonne l'arrêt de tous les daemons
     */
    public function stopAll():void;

    /**
     * Ordonne le démarrage de tous les daemons
     */
    public function startAll():void;

    /**
     * Ordonne le redémarrage de tous les daemons
     */
    public function restartAll():void;

    /**
     * @return array
     */
    public function statusAll():array;

    /**
     * @param string ...$daemons Liste des daemons à arrêter
     */
    public function stop(string ...$daemons):void;

    /**
     * @param string ...$daemons Liste des daemons à démarrer
     */
    public function start(string ...$daemons):void;

    /**
     * @param string ...$daemons Liste des daemons à redemarrer
     */
    public function restart(string ...$daemons):void;

    /**
     * @param string ...$daemons Liste des daemons pour lequels obtenir les status
     * @return array
     */
    public function status(string ...$daemons):array;
}