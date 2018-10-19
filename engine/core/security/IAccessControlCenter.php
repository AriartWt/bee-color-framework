<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/02/18
 * Time: 12:27
 */

namespace wfw\engine\core\security;

use wfw\engine\core\action\IAction;

/**
 * Permet de controller l'accés à une action
 */
interface IAccessControlCenter
{
    /**
     * Ajoute une régle de verification de permissions.
     * @param IAccessRule $rule Règle à ajouter
     */
    public function addRule(IAccessRule $rule):void;

    /**
     * Vérifie les permissions d'accés à l'action $action en appliquant une a à une toutes les
     * règles ajoutées avec addRule(), dans leur ordre d'ajout, jusqu'à ce que toutes les règles
     * soient appliquées, ou que l'une d'entre elle ait retourné null. Retourner null revient à
     * interrompre la chaine de verifications.
     * @param IAction $action Action à tester
     * @return IAccessPermission
     */
    public function checkPermissions(IAction $action):IAccessPermission;
}