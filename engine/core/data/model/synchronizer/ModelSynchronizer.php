<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/06/18
 * Time: 16:09
 */

namespace wfw\engine\core\data\model\synchronizer;


use wfw\engine\core\data\model\snapshoter\IModelSnapshoter;
use wfw\engine\core\data\model\storage\IModelStorage;

/**
 * Class ModelSynchronizer
 *
 * @package wfw\engine\core\data\model\synchronizer
 */
final class ModelSynchronizer implements IModelSynchronizer
{
    /** @var IModelStorage $_storage */
    private $_storage;
    /** @var IModelSnapshoter $_snapshoter */
    private $_snapshoter;

    /**
     * ModelSynchronizer constructor.
     *
     * @param IModelStorage    $storage    Storage à utiliser
     * @param IModelSnapshoter $snapshoter Snapshoter à utiliser
     */
    public function __construct(IModelStorage $storage, IModelSnapshoter $snapshoter)
    {
        $this->_snapshoter = $snapshoter;
        $this->_storage = $storage;
    }

    /**
     * @return IModelStorage
     */
    public function getModelStorage(): IModelStorage { return $this->_storage; }

    /**
     * @return IModelSnapshoter
     */
    public function getModelSnapshoter(): IModelSnapshoter { return $this->_snapshoter; }

    /**
     * Lance la synchronisation
     */
    public function synchronize(): void
    {
        //On met à jour les models du snapshot.
        $this->_snapshoter->updateSnapshot();
        //On remplace chaque model par sa version la plus récente
        //Si des erreurs sont survenues lors de la dernière execution,
        //elles sont effacées et le model repart sur une base saine
        foreach($this->_snapshoter->getModels() as $k=>$model){
            $this->_storage->set(get_class($model),$model);
        }
    }
}