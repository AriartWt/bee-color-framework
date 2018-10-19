<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 09:02
 */

namespace wfw\daemons\kvstore\server\responses;

/**
 *  La requpete n'a pas abouti pour une raison inconnue
 */
final class RequestTimeout extends RequestError
{
    public function __construct()
    {
        parent::__construct(new \Exception("La requête n'a pas pu aboutir, le container à mis trop de temps à répondre."));
    }
}