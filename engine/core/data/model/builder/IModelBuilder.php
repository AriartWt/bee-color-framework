<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/12/17
 * Time: 06:43
 */

namespace wfw\engine\core\data\model\builder;


use wfw\engine\core\data\model\IModel;

/**
 * Interface IModelBuilder
 *
 * @package wfw\daemons\model\builder
 */
interface IModelBuilder
{
    /**
     *  Instancie un model vide
     *
     * @param string $model Classe du model à instancier
     *
     * @return IModel
     */
    public function buildModel(string $model):IModel;
}