<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/01/18
 * Time: 04:48
 */

namespace wfw\daemons\modelSupervisor\server\requests;

use wfw\daemons\modelSupervisor\server\IMSServerRequest;

/**
 *  Implémentation de base d'une MSServerRequest
 */
abstract class AbstractMSServerRequest implements IMSServerRequest
{
    /**
     * @var null|string $_sessionId
     */
    private $_sessionId;

    /**
     * AbstractKVSRequest constructor.
     *
     * @param string|null $sessionId Identifiant de session
     */
    public function __construct(?string $sessionId=null)
    {
        $this->_sessionId = $sessionId;
    }

    /**
     * @return null|string
     */
    public final function getSessionId(): ?string
    {
        return $this->_sessionId;
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