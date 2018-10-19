<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 03:56
 */

namespace wfw\daemons\kvstore\server\environment;

use wfw\daemons\kvstore\server\errors\UserNotFound;

/**
 *  Groupe d'utilisateurs du KVS
 */
final class KVSUserGroup implements IKVSUserGroup
{
    private $_name;
    private $_users;

    /**
     * KVSUserGroup constructor.
     *
     * @param string              $name  Nom du groupe
     * @param IKVSUser[]  $users Liste d'utilisateurs du groupe
     */
    public function __construct(string $name, array $users)
    {
        $this->_name = $name;
        $this->_users = [];
        foreach ($users as $offset=>$user){
            if($user instanceof IKVSUser){
                $this->_users[$user->getName()]=$user;
            }else{
                throw new \InvalidArgumentException("Invalid argument at offset $offset : only object that implements ".IKVSUser::class." are allowed !");
            }
        }
    }

    /**
     *  Retourne l'utilisateur du groupe dont le nom est $name
     *
     * @param string $name Nom de l'utilisateur.
     *
     * @return IKVSUser
     */
    public function getUser(string $name): IKVSUser
    {
        if($this->hasUser($name)){
            return $this->_users[$name];
        }else{
            throw new UserNotFound("Unknwown user $name");
        }
    }

    /**
     * @return IKVSUser[] Liste des utilisateurs appartenant au groupe.
     */
    public function getUsers(): array
    {
        return array_values($this->_users);
    }

    /**
     *  Vérifie la présence d'un utilisateur dans le groupe
     *
     * @param string $name Nom de l'utilisateur à tester
     *
     * @return bool
     */
    public function hasUser(string $name): bool
    {
        return isset($this->_users[$name]);
    }

    /**
     * @return string Nom du groupe
     */
    public function getName(): string
    {
        return $this->_name;
    }
}