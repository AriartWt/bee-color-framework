<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 03:52
 */

namespace wfw\daemons\kvstore\server\environment;

use wfw\daemons\kvstore\server\errors\KVSServerFailure;

/**
 *  Utilisateur des service KVS
 */
final class KVSUser implements IKVSUser
{
    private $_name;
    private $_password;

    /**
     * KVSUser constructor.
     *
     * @param string $name     Nom de l'utilisateur (pas de : ni de /)
     * @param string $password Mot de passe de l'utilisateur
     */
    public function __construct(string $name, string $password)
    {
        if(!is_bool(strpos($name,"/")) || !is_bool(strpos($name,":"))){
            throw new KVSServerFailure("The following chars are not allowed in user name: ':/' !");
        }
        $this->_name = $name;
        $this->_password = $password;
    }

    /**
     * @return string Nom de l'utilisateur
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     *  Teste la validité d'un mot de passe.
     *
     * @param string $password Mot de passe à tester
     *
     * @return bool
     */
    public function matchPassword(string $password): bool
    {
        return $this->_password === $password;
    }
}