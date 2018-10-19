<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/01/18
 * Time: 10:24
 */

namespace wfw\engine\core\data\model\loaders;

use wfw\engine\core\data\model\IModel;

/**
 *  Permet de charger un model
 */
interface IModelLoader
{
    /**
     *  Charge un modèle
     *
     * @param string $model Clé permettant d'identifier le modèle à charger
     *
     * @return IModel|null
     */
    public function load(string $model):?IModel;

    /**
     *  Retourne la liste des models acceptés par le loader
     * @return array
     */
    public function getModelList():array;
}