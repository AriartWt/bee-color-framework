<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:55
 */

namespace wfw\daemons\kvstore\server\containers\request\write;

use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;
use wfw\daemons\kvstore\server\KVSModes;

/**
 *  Change le mode de stockage d'une clé
 */
final class ChangeStorageModeRequest extends AbstractWriteRequest
{
    /**
     * @var int|null $_storageMode
     */
    private $_storageMode;

    /**
     * ChangeStorageMode constructor.
     *
     * @param string     $sessId      Identifiant de session
     * @param StorageKey $key         Clé à modifier
     * @param int|null   $storageMode Nouveau mode de stockage. Si null, le mod ed estockage du container sera utilisé.
     */
    public function __construct(string $sessId,StorageKey $key, ?int $storageMode)
    {
        parent::__construct($sessId,$key);
        if(!is_null($storageMode) && !KVSModes::existsValue($storageMode)){
            throw new \InvalidArgumentException("Unknown storage mode $storageMode");
        }
        $this->_storageMode = $storageMode;
    }

    /**
     * @return int|null
     */
    public function getStorageMode(): ?int
    {
        return $this->_storageMode;
    }
}