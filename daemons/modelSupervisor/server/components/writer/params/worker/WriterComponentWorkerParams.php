<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/01/18
 * Time: 06:19
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\params\worker;

use wfw\daemons\modelSupervisor\server\components\writer\modelManager\IModelManager;
use wfw\engine\core\data\model\snapshoter\IModelSnapshoter;
use wfw\engine\core\data\model\synchronizer\IModelSynchronizer;

/**
 *  Paramètres du worker.
 */
final class WriterComponentWorkerParams
{
    /** @var IModelManager $_modelManager */
    private $_modelManager;
    /** @var string $_serverKey */
    private $_serverKey;
    /** @var IModelSnapshoter $_snapshoter */
    private $_synchronizer;

    /**
     * WriteModelWorkerParams constructor.
     *
     * @param string             $serverKey    Clé serveur
     * @param IModelManager      $modelManager Gestionnaire de models
     * @param IModelSynchronizer $synchronizer Synchronizer de models
     */
    public function __construct(
        string $serverKey,
        IModelManager $modelManager,
        IModelSynchronizer $synchronizer
    ){
        $this->_serverKey = $serverKey;
        $this->_modelManager = $modelManager;
        $this->_synchronizer = $synchronizer;
    }

    /**
     * @return IModelManager
     */
    public function getModelManager():IModelManager{
        return $this->_modelManager;
    }

    /**
     * @return string
     */
    public function getServerKey():string{
        return $this->_serverKey;
    }

    /**
     * @return IModelSynchronizer
     */
    public function getModelSynchronizer():IModelSynchronizer{
        return $this->_synchronizer;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function matchServerKey(string $key): bool
    {
        return $this->_serverKey === $key;
    }
}