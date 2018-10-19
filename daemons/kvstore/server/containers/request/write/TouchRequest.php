<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:58
 */

namespace wfw\daemons\kvstore\server\containers\request\write;


use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;

/**
 *  Réactualise la date de péremption d'une clé.
 */
final class TouchRequest extends AbstractWriteRequest
{
    /**
     * @var null|float $_ttl
     */
    private $_ttl;

    /**
     * TouchRequest constructor.
     *
     * @param StorageKey $key Clé concernée
     * @param null|float $ttl Nouveau temps de vie. Si 0 : la durée de vie de la clé n'est plus limitée
     */
    public function __construct(StorageKey $key,?float $ttl=null)
    {
        parent::__construct($key);
        $this->_ttl = $ttl;
    }

    /**
     * @return null|float
     */
    public function getTtl(): ?float
    {
        return $this->_ttl;
    }
}