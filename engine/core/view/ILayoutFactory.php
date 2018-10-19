<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/02/18
 * Time: 07:09
 */

namespace wfw\engine\core\view;

/**
 * Permet de créer des Layout.
 */
interface ILayoutFactory
{
    /**
     * Crée un layout
     * @param string $layoutClass
     * @param array  $params
     * @return ILayout
     */
    public function create(string $layoutClass, array $params=[]):ILayout;
}