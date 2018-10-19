<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/01/18
 * Time: 23:26
 */

namespace wfw\daemons\modelSupervisor\server\responses;

/**
 *  Le ocmposant a mis trop de temps à répondre.
 */
final class RequestTimeout extends RequestError
{
    public function __construct()
    {
        parent::__construct(new \Exception("La requête n'a pas pu aboutir, le composant à mis trop de temps à répondre."));
    }
}