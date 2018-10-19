<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 07:04
 */

namespace wfw\daemons\kvstore\server\containers\data;

use wfw\daemons\kvstore\server\containers\data\errors\UnsupportedStorageMode;
use wfw\daemons\kvstore\server\containers\data\storages\InMemoryOnly;
use wfw\daemons\kvstore\server\containers\data\storages\InMemoryPersistedOnDisk;
use wfw\daemons\kvstore\server\containers\data\storages\OnDiskOnly;
use wfw\daemons\kvstore\server\KVSModes;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;

/**
 *  Permet de lier les modes de la classe KVSModes avec des IKVSStorageManager
 */
final class KVSStorageModeInflector implements IKVSStorageModeInflector
{
    /**
     * @var array $_modes
     */
    private $_modes;

    /**
     * KVSStorageModeInflector constructor.
     *
     * @param string                   $containerPath
     * @param null|ISerializer $serializer
     */
    public function __construct(string $containerPath,?ISerializer $serializer=null)
    {
        $serializer = $serializer ?? new LightSerializer(
            new GZCompressor(),
            new PHPSerializer()
            );
        $this->_modes=[];
        $this->_modes[KVSModes::IN_MEMORY_ONLY] = new InMemoryOnly();
        $this->_modes[KVSModes::ON_DISK_ONLY] = new OnDiskOnly($containerPath,$serializer);
        $this->_modes[KVSModes::IN_MEMORY_PERSISTED_ON_DISK] =
            new InMemoryPersistedOnDisk(
                new InMemoryOnly(),
                new OnDiskOnly($containerPath,$serializer)
            );
    }

    /**
     * @return KVSStorageModeManager[] Tous les gestionnaires de données
     */
    public function getAll(): array
    {
        return $this->_modes;
    }

    /**
     * @param int $mode Mode de stockage
     *
     * @return KVSStorageModeManager Gestionnaires de données concernés
     */
    public function getStorageModeManager(int $mode): KVSStorageModeManager
    {
        if(isset($this->_modes[$mode])){
            return $this->_modes[$mode];
        }else{
            throw new UnsupportedStorageMode("Unknown or unsupported storage mode $mode");
        }
    }
}