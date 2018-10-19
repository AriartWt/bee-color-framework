<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 22/02/18
 * Time: 08:28
 */

namespace wfw\engine\core\view;

/**
 * Permet de créer une vue.
 */
interface IViewFactory
{
    /**
     * @param string $viewClass Classe de la vue à créer.
     * @param array  $params    Paramètres de création
     * @return IView
     */
    public function create(string $viewClass, array $params = []):IView;
}