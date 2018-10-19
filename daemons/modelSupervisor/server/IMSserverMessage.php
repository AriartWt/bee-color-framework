<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/01/18
 * Time: 11:22
 */

namespace wfw\daemons\modelSupervisor\server;

/**
 * Message de l'un des composant du MS (client, worker, serveur) : requête ou réponse.
 */
interface IMSserverMessage
{
    /**
     * @return mixed Données du message.
     */
    public function getData();

    /**
     * @return mixed Paramètres du message
     */
    public function getParams();
}