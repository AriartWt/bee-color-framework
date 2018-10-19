<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/04/18
 * Time: 00:34
 */

namespace wfw\cli\backup;

/**
 * Permet de créer/charger des backups.
 */
interface IBackup
{
    /**
     * @return string Localisation du backup
     */
    public function getLocation():string;

    /**
     * @param string $path nouvelle localisation du backup
     */
    public function setLocation(string $path):void;
    /**
     * Crée un backup
     */
    public function make():void;

    /**
     * @return null|float Date de création du backup avec la fonction make()
     */
    public function makeDate():?float;

    /**
     * Restore l'application avec le backup courant
     */
    public function load():void;

    /**
     * Supprime le backup courant
     */
    public function remove():void;
}