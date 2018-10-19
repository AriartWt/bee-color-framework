<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 08:44
 */

namespace wfw\daemons\kvstore\server\requests;

use wfw\daemons\kvstore\server\KVSModes;

/**
 *  Requête de connexion
 */
final class LoginRequest extends AbstractKVSRequest
{
    /**
     * @var string $_login
     */
    private $_login;
    /**
     * @var string $_password
     */
    private $_password;
    /**
     * @var string $_container
     */
    private $_container;
    /**
     * @var int $_defaultStorageMode
     */
    private $_defaultStorageMode;

    /**
     * ConnectionRequest constructor.
     *
     * @param string $container
     * @param string $login
     * @param string $password
     * @param string $defaultStorageMode
     */
    public function __construct(string $container,string $login, string $password,?string $defaultStorageMode=null)
    {
        parent::__construct(null);
        $this->_login = $login;
        $this->_password = $password;
        $this->_container = $container;
        if(!is_null($defaultStorageMode)){
            $this->_defaultStorageMode = KVSModes::get($defaultStorageMode);
        }else{
            $this->_defaultStorageMode = null;
        }
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->_login;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->_password;
    }

    /**
     * @return string
     */
    public function getContainer(): string
    {
        return $this->_container;
    }

    /**
     * @return int
     */
    public function getDefaultStorageMode(): ?int
    {
        return $this->_defaultStorageMode;
    }
}