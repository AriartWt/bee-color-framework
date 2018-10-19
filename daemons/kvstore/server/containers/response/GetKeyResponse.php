<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 08:34
 */

namespace wfw\daemons\kvstore\server\containers\response;

use wfw\daemons\kvstore\server\responses\AbstractKVSResponse;

/**
 *  Réponse à une requête de type GetKeyRequest
 */
final class GetKeyResponse extends AbstractKVSResponse
{
    /**
     * @var mixed $_value
     */
    private $_value;

    /**
     * GetKeyResponse constructor.
     *
     * @param string $value Valeur associée à la clé (sérialisée)
     */
    public function __construct(string $value)
    {
        $this->_value = $value;
    }

    /**
     * @return string
     */
    public function getData():string
    {
        return $this->_value;
    }
}