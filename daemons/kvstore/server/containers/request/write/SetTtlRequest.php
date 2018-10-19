<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:49
 */

namespace wfw\daemons\kvstore\server\containers\request\write;

use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;

/**
 *  Modifie le temps de vie d'une clé.
 */
final class SetTtlRequest extends AbstractWriteRequest
{
    /**
     * @var float $_ttl
     */
    private $_ttl;

    /**
     * SetTtlRequest constructor.
     *
     * @param string     $sessId Identifiant de session
     * @param StorageKey $key    Clé concernée
     * @param float      $ttl    Temps de vie de la clé. Si 0 : la clé n'a plus de limite de vie.
     */
    public function __construct(string $sessId,StorageKey $key,float $ttl)
    {
        parent::__construct($sessId,$key);
        $this->_ttl = $ttl;
    }

    /**
     * @return float
     */
    public function getTtl(): float
    {
        return $this->_ttl;
    }
}