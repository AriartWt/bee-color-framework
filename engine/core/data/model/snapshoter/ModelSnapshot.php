<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 03/01/18
 * Time: 08:59
 */

namespace wfw\engine\core\data\model\snapshoter;

use wfw\engine\core\data\model\IModel;

/**
 * Class ModelSnapshot
 *
 * @package wfw\daemons\model\snapshoter
 */
class ModelSnapshot
{
    private $_lastEventNumber;
    private $_models=[];

    /**
     * ModelSnapshot constructor.
     *
     * @param array $models
     * @param int   $lastEventNumber
     */
    public function __construct(array $models,int $lastEventNumber)
    {
        $this->_lastEventNumber = $lastEventNumber;
        foreach($models as $k=>$model){
            if(!($model instanceof IModel)){
                throw new \InvalidArgumentException("Invalide model at offset $k : all items have to be instanceof ".IModel::class);
            }else{
                $this->_models[get_class($model)] = $model;
            }
        }
    }

    /**
     * @return int
     */
    public function getLastEventNumber(): int
    {
        return $this->_lastEventNumber;
    }

    /**
     * @return array
     */
    public function getModels(): array
    {
        return $this->_models;
    }
}