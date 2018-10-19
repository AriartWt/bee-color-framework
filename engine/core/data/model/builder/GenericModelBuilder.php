<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/12/17
 * Time: 06:44
 */

namespace wfw\engine\core\data\model\builder;

use wfw\engine\core\data\model\arithmeticSearch\ArithmeticParser;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSearcher;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSolver;
use wfw\engine\core\data\model\IModel;

/**
 * Class GenericModelBuilder
 *
 * @package wfw\daemons\model\builder
 */
class GenericModelBuilder implements IModelBuilder
{
    /**
     * @param string $model
     *
     * @return IModel
     */
    public function buildModel(string $model): IModel
    {
        return new $model(new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser())));
    }
}