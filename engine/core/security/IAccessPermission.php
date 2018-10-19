<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/02/18
 * Time: 12:34
 */

namespace wfw\engine\core\security;
use wfw\engine\core\response\IResponse;

/**
 * Permission d'acces.
 */
interface IAccessPermission
{
    /**
     * @return bool Permission accordée ou non.
     */
    public function isGranted():bool;

    /**
     * @return null|string Code
     */
    public function getCode():?string;

    /**
     * @return null|string Message
     */
    public function getMessage():?string;

    /**
     * @return null|IResponse Réponse
     */
    public function getResponse():?IResponse;
}