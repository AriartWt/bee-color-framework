<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/02/18
 * Time: 06:23
 */

namespace wfw\engine\core\response;

use wfw\engine\core\response\IResponse;
use wfw\engine\core\view\IView;

/**
 * Permet de traiter une réponse à une action en préparant la vue à retourner au client.
 */
interface IResponseHandler
{
    /**
     * @param IResponse $response Réponse créer par l'ActionHandler
     * @return IView Vue à retourner au client
     */
    public function handleResponse(IResponse $response):IView;
}