<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 03:13
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\responses;

use wfw\daemons\modelSupervisor\server\components\responses\ComponentResponse;

/**
 * @brief Réponse du WriterComponent
 */
final class WriterResponse extends ComponentResponse implements IWriterResponse {

    /**
     * @return mixed Données du message.
     */
    public function getData()
    {
        return null;
    }

    /**
     * @return mixed Paramètres du message
     */
    public function getParams()
    {
        return null;
    }
}