<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 08:48
 */

namespace wfw\daemons\kvstore\server\requests;

/**
 *  Demande la destruction d'une session.
 */
final class LogoutRequest extends AbstractKVSRequest
{
    /**
     * DeconnectionRequest constructor.
     *
     * @param string $sessionId Identifiant de session à déconnecter
     */
    public function __construct(string $sessionId)
    {
        parent::__construct($sessionId);
    }
}