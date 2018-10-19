<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/11/17
 * Time: 05:31
 */

namespace wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors;

/**
 *  Levée lorsque le teste de connexion ping ne renvoie pas +PONG
 */
class ConnectionFailed extends RedisFailure
{

}