<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:44
 */

namespace wfw\daemons\kvstore\server\containers\request\write;

use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;
use wfw\daemons\kvstore\server\KVSModes;

/**
 *  Requête set
 */
final class SetRequest extends AbstractWriteRequest
{
    /**
     * @var float $_ttl
     */
    private $_ttl;
    /**
     * @var mixed $_data
     */
    private $_data;
    /**
     * @var int|null $_storageMode
     */
    private $_storageMode;

    /**
     * SetRequest constructor.
     *
     * @param string     $sessId      Identifiant de session
     * @param StorageKey $key         Clé de stockage
     * @param string     $data        Données à stocker.
     * @param float      $ttl         (optionnel défaut : 0) Temps de vie de la clé. Si 0 : temps de vie non limité
     * @param int|null   $storageMode (optionnel) Mode de stockage de la clé. Par défaut, le mode de stockage du container est utilisé.
     */
    public function __construct(string $sessId,StorageKey $key,string $data,float $ttl=0,?int $storageMode=null)
    {
        parent::__construct($sessId,$key);
        $this->_data = $data;
        $this->_ttl = $ttl;
        if(!is_null($storageMode) && !KVSModes::existsValue($storageMode)){
            throw new \InvalidArgumentException("Unknown storage mode $storageMode");
        }
        $this->_storageMode = $storageMode;
    }

    /**
     * @return float
     */
    public function getTtl(): float
    {
        return $this->_ttl;
    }

    /**
     * @return null|string
     */
    public function getData():?string
    {
        return $this->_data;
    }

    /**
     * @return SetRequest
     */
    public function getParams()
    {
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStorageMode(): ?int
    {
        return $this->_storageMode;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            "_key",
            "_ttl",
            "_storageMode"
        ];
    }
}