<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 08:25
 */

namespace wfw\daemons\kvstore\server;

use wfw\daemons\kvstore\socket\io\KVSSocketIO;


/**
 *  Requête créée par le serveur KVS lors de la reception d'une requête d'un client destinée
 *        à un container.
 */
interface IKVSQuery
{
    /**
     * @return KVSSocketIO Client ayant envoyé la requête
     */
    public function getIO():KVSSocketIO;

    /**
     * @return IKVSInternalRequest Requête interne envoyée à l'un des worker.
     */
    public function getInternalRequest():IKVSInternalRequest;

    /**
     * @return int Date d'expiration de la requête.
     */
    public function getExpirationDate():int;

    /**
     * @return int Date à laquelle la requête a été créée
     */
    public function getGenerationDate() : int;
}