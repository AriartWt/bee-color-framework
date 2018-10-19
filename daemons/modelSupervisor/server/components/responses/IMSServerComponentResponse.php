<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/01/18
 * Time: 08:28
 */

namespace wfw\daemons\modelSupervisor\server\components\responses;

use wfw\daemons\modelSupervisor\server\IMSServerRequest;
use wfw\daemons\modelSupervisor\server\IMSServerResponse;

/**
 *  Réponse d'un module à une requête client
 */
interface IMSServerComponentResponse extends IMSServerRequest
{
    /**
     *  Retourne l'identifiant de la requête envoyée par le ModelServer au WriteModule
     * @return string
     */
    public function getQueryId():string;

    /**
     * @return null|IMSServerResponse
     */
    public function getResponse():?IMSServerResponse;
}