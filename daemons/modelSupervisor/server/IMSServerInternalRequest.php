<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/01/18
 * Time: 01:43
 */

namespace wfw\daemons\modelSupervisor\server;

/**
 *  Requête interne entre le MSServer et ses components.
 */
interface IMSServerInternalRequest extends IMSserverMessage
{
    /**
     * @return string Clé générée par le serveur
     */
    public function getServerKey():string;

    /**
     * @return string Identifiant de la requête
     */
    public function getQueryId():string;

    /**
     * @return string Nom de l'utilisateur à l'origine de la requête.
     */
    public function getUserName():string;

    /**
     * @return string La classe correspondant à la requête
     */
    public function getRequestClass():string;
}