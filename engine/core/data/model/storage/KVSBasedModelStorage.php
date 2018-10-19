<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/01/18
 * Time: 07:20
 */

namespace wfw\engine\core\data\model\storage;

use wfw\engine\core\data\DBAccess\NOSQLDB\kvs\KVSAccess;
use wfw\engine\core\data\model\IModel;

/**
 *  Gestionnaire de stockage de model basé sur KVStore
 */
final class KVSBasedModelStorage implements IModelStorage
{
    private $_access;
    private $_defaultStorage;

    /**
     * IKVSBasedModelStorage constructor.
     *
     * @param KVSAccess $access
     * @param int       $defaultStorage
     */
    public function __construct(KVSAccess $access, int $defaultStorage = self::IN_MEMORY_PERSISTED_ON_DISK)
    {
        $this->_access = $access;
        $this->_defaultStorage = $defaultStorage;
    }

    /**
     * @param string $key
     *
     * @return null|IModel
     */
    public function get(string $key): ?IModel
    {
        $model = $this->_access->get($key);
        if(is_null($key)){
            return null;
        }else{
            return unserialize($model);
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->_access->exists($key);
    }

    /**
     * @param string         $key
     * @param IModel $model
     * @param int            $storageMode
     */
    public function set(string $key, IModel $model, ?int $storageMode = null)
    {
        $this->_access->set($key,$model,0,$storageMode??$this->_defaultStorage);
    }

    /**
     * @param string $key
     */
    public function remove(string $key)
    {
        $this->_access->remove($key);
    }
}