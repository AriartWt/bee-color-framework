<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 09:14
 */

namespace wfw\daemons\kvstore\server\requests;

/**
 *  Requête KVS de basse (gére le numéro de session)
 */
abstract class AbstractKVSRequest implements IKVSRequest
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
     * @return null
     */
    public function getData()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getParams()
    {
        return null;
    }
}