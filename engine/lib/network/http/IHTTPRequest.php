<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 03:10
 */

namespace wfw\engine\lib\network\http;

/**
 *  requette HTTP
 */
interface IHTTPRequest
{
    /**
     *  Envoie une requête HTTP et retourne la réponse
     * @return string
     */
    public function send():string;
}