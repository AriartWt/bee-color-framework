<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 03:11
 */

namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

use wfw\daemons\modelSupervisor\server\components\requests\IShutdownComponentRequest;

/**
 * @brief Demande au worker de s'éteindre proprement.
 */
final class ShutdownWriterRequest implements IShutdownComponentRequest,IWriterAdminRequest {
    /**
     * @return null|string Identifiant de session
     */
    public function getSessionId(): ?string
    {
        return null;
    }

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