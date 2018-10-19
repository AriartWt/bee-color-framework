<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/11/17
 * Time: 08:20
 */

namespace wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors;

/**
 *  Levée lors d'une erreur dans un repository utilisant Redis.
 */
class RedisFailure extends \Exception {}