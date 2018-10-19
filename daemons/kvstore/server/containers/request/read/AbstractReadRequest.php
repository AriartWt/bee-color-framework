<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:24
 */

namespace wfw\daemons\kvstore\server\containers\request\read;

use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;
use wfw\daemons\kvstore\server\requests\AbstractKVSRequest;

/**
 *  Base d'implémentation pour une requête en lecture
 */
abstract class AbstractReadRequest extends AbstractKVSRequest implements IKVSReadContainerRequest
{
    /**
     * @var string $_key
     */
    private $_key;

    /**
     * AbstractReadRequest constructor.
     *
     * @param string     $sessId Identifiant de session
     * @param StorageKey $key    Clé de lecture
     */
    public function __construct(string $sessId,StorageKey $key)
    {
        parent::__construct($sessId);
        $this->_key = (string)$key;
    }

    /**
     * @return string
     */
    public function getKey():string{
        return $this->_key;
    }
}