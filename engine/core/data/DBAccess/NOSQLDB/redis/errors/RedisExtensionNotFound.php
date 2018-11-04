<?php
namespace wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors;

/**
 *  Levée lorsque l'extension php-redis n'est pas installée
 */
class RedisExtensionNotFound extends RedisFailure {}