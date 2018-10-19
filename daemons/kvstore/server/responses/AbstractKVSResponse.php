<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/01/18
 * Time: 02:35
 */

namespace wfw\daemons\kvstore\server\responses;


/**
 * Implementation de base des réponses.
 */
abstract class AbstractKVSResponse implements IKVSResponse
{

    /**
     * @return mixed Données du message
     */
    public function getData()
    {
        return null;
    }

    /**
     * @return mixed Paramètres du message.
     */
    public function getParams()
    {
        return null;
    }
}