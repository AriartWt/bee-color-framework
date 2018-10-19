<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/02/18
 * Time: 08:15
 */

namespace wfw\engine\core\view;

use wfw\engine\core\action\IAction;

/**
 * Permet de trouver la classe d'un layout à partir d'une IAction
 */
interface ILayoutResolver
{
    /**
     * @param IAction $action Action permettant de determiner le layout
     * @return ILayout
     */
    public function resolve(IAction $action):ILayout;
}