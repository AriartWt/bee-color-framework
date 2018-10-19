<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 01:51
 */

namespace wfw\daemons\kvstore\server\responses;

/**
 *  La demande de connexion a été approuvée.
 */
final class AccessGranted extends AbstractKVSResponse
{
    /**
     * @var string $_sessionId
     */
    private $_sessionId;

    /**
     * AccessGranted constructor.
     *
     * @param string $sessionId Identifiant de session attribué.
     */
    public function __construct(string $sessionId)
    {
        $this->_sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionId():string{
        return $this->_sessionId;
    }
}