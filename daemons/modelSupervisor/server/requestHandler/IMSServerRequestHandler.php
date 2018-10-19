<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/01/18
 * Time: 10:19
 */

namespace wfw\daemons\modelSupervisor\server\requestHandler;

use wfw\daemons\modelSupervisor\server\IMSServerQuery;

/**
 *  Reçoit et réagit à une requête.
 */
interface IMSServerRequestHandler
{
    /**
     *  Reçoit et traite la requête
     *
     * @param IMSServerQuery $request
     */
    public function handleModelManagerQuery(IMSServerQuery $request);
}