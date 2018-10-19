<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 08:33
 */

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