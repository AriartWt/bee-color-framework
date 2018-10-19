<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/12/17
 * Time: 13:27
 */

namespace wfw\engine\lib\network\socket\protocol;

/**
 *  Défini un protocole de communication pour des sockets.
 */
interface ISocketProtocol
{
    /**
     * @param resource $socket Socket sur laquelle il faut lire les données
     *
     * @return string
     */
    public function read($socket):string;

    /**
     * @param resource $socket Socket sur laquelle il faut écrire les données
     * @param string   $data   Données à écrire dans la socket
     */
    public function write($socket,string $data);
}