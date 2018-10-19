<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/01/18
 * Time: 07:14
 */

namespace wfw\engine\core\data\model\storage;

use wfw\engine\core\data\model\IModel;

/**
 *  Méthodes utilisées pour persister des models.
 */
interface IModelStorage
{
    public const ON_DISK_ONLY = 1;
    public const IN_MEMORY_ONLY = 2;
    public const IN_MEMORY_PERSISTED_ON_DISK = 4;

    /**
     * @param string $key
     *
     * @return null|IModel
     */
    public function get(string $key):?IModel;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key):bool;

    /**
     * @param string         $key
     * @param IModel $model
     * @param int            $storageMode
     */
    public function set(string $key, IModel $model, int $storageMode=self::IN_MEMORY_PERSISTED_ON_DISK);

    /**
     * @param string $key
     */
    public function remove(string $key);
}