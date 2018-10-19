<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 01:20
 */

namespace wfw\daemons\kvstore\server\environment;

use wfw\daemons\kvstore\server\KVSModes;


/**
 *  Environnement KVS (Utilisateurs et groupes, containers, sessions...)
 */
interface IKVSServerEnvironment
{
    /**
     *  Vérifie les droit d'execution d'une requête d'administration du serveur pour un utilisateur donné.
     *
     * @param string $userName     Nom de l'utilisateur
     * @param string $requestClass Nom de la classe de la requête à tester
     *
     * @return bool
     */
    public function isAdminAccessGranted(string $userName,string $requestClass):bool;
    /**
     * @param string $name Nom du container
     *
     * @return IKVSContainer
     */
    public function getContainer(string $name):IKVSContainer;

    /**
     *  Teste l'existence d'un container
     *
     * @param string $name Nom du container à tester
     *
     * @return bool
     */
    public function existsContainer(string $name):bool;

    /**
     * @return IKVSContainer[] Liste des containers
     */
    public function getContainers():array;

    /**
     *  Retourne un utilisateur grâce à son nom.
     *
     * @param string $name Nom de l'utilisateur
     *
     * @return IKVSUser
     */
    public function getUser(string $name):IKVSUser;

    /**
     *  Teste l'existence d'un utilisateur
     *
     * @param string $name Nom de l'utilisateur à tester
     *
     * @return bool
     */
    public function existsUser(string $name):bool;

    /**
     *  Retourne un groupe d'utilisateur
     *
     * @param string $name Nom du groupe
     *
     * @return IKVSUserGroup
     */
    public function getUserGroup(string $name):IKVSUserGroup;

    /**
     *  Teste l'existence d'un groupe d'utilisateur
     *
     * @param string $name Nom du groupe à tester
     *
     * @return bool
     */
    public function existsUserGroup(string $name):bool;

    /**
     *  Crée une session pour un utilisateur si ses informations de connexion sont valides.
     *
     * @param string $container            Container auquel l'utilisaeur tente la connexion
     * @param string $login                Login de l'utilisateur
     * @param string $password             Mot de passe de l'utilisateur
     * @param int    $default_storage_mode Type de stoclage par défaut
     *
     * @return null|string Identifiant de session si la session a été créée, null sinon.
     */
    public function createSessionForUser(string $container, string $login, string $password, ?int $default_storage_mode = KVSModes::IN_MEMORY_PERSISTED_ON_DISK): ?string;

    /**
     *  Retourne une session grace à son identifiant.
     *
     * A chaque fois que la fonction est appelée, le temps avant suppression de la session doit être remis à 0.
     *
     * @param string $sessionId Identifiant de session
     *
     * @return IKVSUserSession|null
     */
    public function getUserSession(string $sessionId): ?IKVSUserSession;

    /**
     *  Remet à 0 le compteur de suppression de la session.
     *
     * @param string $sessionId Identifiant de la session.
     */
    public function touchUserSession(string $sessionId):void;

    /**
     *  Teste l'existence d'une session
     *
     * @param string $sessionId Identifiant de la session à tester
     *
     * @return bool
     */
    public function existsUserSession(string $sessionId):bool;

    /**
     *  Détruit la session d'un utilisateur
     * @param string $sessionId Session à détruire.
     */
    public function destroyUserSession(string $sessionId);

    /**
     *  Supprime les sessions inactives depuis un certain temps.
     */
    public function destroyOutdatedSessions():void;

    /**
     * @return string Chemin du dossier d'enregistrement des containers par défaut.
     */
    public function getDefaultDBPath():string;
}