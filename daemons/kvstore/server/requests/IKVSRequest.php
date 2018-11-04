<?php
namespace wfw\daemons\kvstore\server\requests;

use wfw\daemons\kvstore\server\IKVSMessage;

/**
 *  Requête faite au KVSServer
 */
interface IKVSRequest extends IKVSMessage{
    /**
     *  Retourne l'identifiant de session de l'utilisateur
     * @return null|string
     */
    public function getSessionId():?string;
}