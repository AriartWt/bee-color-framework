<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 05:57
 */

namespace wfw\daemons\kvstore\server\containers\data\storages;

use wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied;

/**
 *  Clé de stockage.
 */
final class StorageKey
{
    /**
     * @var string $_key
     */
    private $_key;

    /**
     * StorageKey constructor.
     *
     * @param string $key
     *
     * @throws InvalidKeySupplied
     */
    public function __construct(string $key)
    {
        if(preg_match("/^(([a-zA-Z0-9:_\\\\-])+(\/{0,1}([a-zA-Z0-9:_\\\\-]+)))+$/",$key)){
            $this->_key = $key;
        }else{
            throw new InvalidKeySupplied("$key does'nt match /^(([a-zA-Z0-9:_\\\\-])+(\/{0,1}([a-zA-Z0-9:_\\\\-]+)))+$/ !");
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_key;
    }
}