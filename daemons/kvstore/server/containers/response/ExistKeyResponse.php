<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:31
 */

namespace wfw\daemons\kvstore\server\containers\response;

use wfw\daemons\kvstore\server\responses\AbstractKVSResponse;

/**
 *  Réponse à une requête de type ExistKeyRequest
 */
final class ExistKeyResponse extends AbstractKVSResponse
{
    /**
     * @var bool $_exists
     */
    private $_exists;

    /**
     * ExistKeyResponse constructor.
     *
     * @param bool $exists True si la clé existe, false sinon
     */
    public function __construct(bool $exists)
    {
        $this->_exists = $exists;
    }

    /**
     * @return bool
     */
    public function exists():bool{
        return $this->_exists;
    }
}