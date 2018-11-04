<?php
namespace wfw\engine\core\data\DBAccess\NOSQLDB\redis\errors;

/**
 *  Levée lorsque le teste de connexion ping ne renvoie pas +PONG
 */
class ConnectionFailed extends RedisFailure {}