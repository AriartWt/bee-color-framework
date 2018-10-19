<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/06/18
 * Time: 16:04
 */

namespace wfw\engine\core\data\model\synchronizer;
use wfw\engine\core\data\model\snapshoter\IModelSnapshoter;
use wfw\engine\core\data\model\storage\IModelStorage;

/**
 * Permet de mettre à jour les Models d'un ModelStorage grâce à la création/mise à jour d'un
 * snapshot de models.
 */
interface IModelSynchronizer
{
    /**
     * @return IModelStorage
     */
    public function getModelStorage():IModelStorage;

    /**
     * @return IModelSnapshoter
     */
    public function getModelSnapshoter():IModelSnapshoter;

    /**
     * Lance la synchronisation
     */
    public function synchronize():void;
}