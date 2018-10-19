<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 22/01/18
 * Time: 08:41
 */

namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\daemons\modelSupervisor\server\requests\admin\IMSServerAdminRequest;
use wfw\engine\core\data\model\loaders\IModelLoader;

/**
 *  Environnement d'execution d'un serveur de model.
 */
interface IMSServerEnvironment
{
    /**
     * @return string Repertoire de travail du serveur.
     */
    public function getWorkingDir():string;

    /**
     * @return IModelLoader Objet permettant de charger un model
     */
    public function getModelLoader():IModelLoader;

    /**
     * @param string $name Nom du composant à tester
     * @return bool True si le composant existe, false sinon
     */
    public function existsComponent(string $name):bool;

    /**
     * @param string $name Nom du composant à obtenir
     * @return IMSServerClientComponent
     */
    public function getComponent(string $name):IMSServerClientComponent;

    /**
     * @return IMSServerClientComponent[] Retourne la liste des composants du MSServer
     */
    public function getComponents():array;

    /**
     *  Vérifie les droit d'execution d'une requête d'administration du serveur pour un utilisateur donné.
     *
     * @param string                        $userName Nom de l'utilisateur
     * @param IMSServerAdminRequest $request  Requête à tester
     *
     * @return bool
     */
    public function isAdminAccessGranted(string $userName,IMSServerAdminRequest $request):bool;

    /**
     *  Retourne un utilisateur grâce à son nom.
     *
     * @param string $name Nom de l'utilisateur
     *
     * @return IMSServerUser
     */
    public function getUser(string $name):IMSServerUser;

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
     * @return IMSServerUserGroup
     */
    public function getUserGroup(string $name):IMSServerUserGroup;

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
     * @param string $login                Login de l'utilisateur
     * @param string $password             Mot de passe de l'utilisateur
     *
     * @return null|string Identifiant de session si la session a été créée, null sinon.
     */
    public function createSessionForUser(string $login, string $password): ?string;

    /**
     *  Retourne une session grace à son identifiant.
     *
     * A chaque fois que la fonction est appelée, le temps avant suppression de la session doit être remis à 0.
     *
     * @param string $sessionId Identifiant de session
     *
     * @return IMSServerSession|null
     */
    public function getUserSession(string $sessionId): ?IMSServerSession;

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
}