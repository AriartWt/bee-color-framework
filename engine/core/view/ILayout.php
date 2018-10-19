<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/02/18
 * Time: 06:39
 */

namespace wfw\engine\core\view;

/**
 * Layout
 */
interface ILayout extends IView
{
    /**
     * @param IView $view Vue à rendre dans le layout.
     */
    public function setView(IView $view):void;
}