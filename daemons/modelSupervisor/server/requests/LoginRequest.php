<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/01/18
 * Time: 23:36
 */

namespace wfw\daemons\modelSupervisor\server\requests;

/**
 *  Requête de connexion
 */
final class LoginRequest extends AbstractMSServerRequest
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
     * LoginRequest constructor.
     *
     * @param string $login    Login de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     */
    public function __construct(string $login, string $password)
    {
        parent::__construct(null);
        $this->_login = $login;
        $this->_password = $password;
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
}