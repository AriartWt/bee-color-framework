<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/01/18
 * Time: 23:23
 */

namespace wfw\daemons\kvstore\server;

/**
 * Représente un message KVS (requête ou réponse)
 */
interface IKVSMessage
{
    /**
     * @return mixed Données du message
     */
    public function getData();

    /**
     * @return mixed Paramètres du message.
     */
    public function getParams();
}