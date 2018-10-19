<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/01/18
 * Time: 10:17
 */

namespace wfw\daemons\kvstore\server\errors;

/**
 *  Le container sur laquelle le serveur tente d'executer une requête n'est pas joignable.
 */
final class InactiveKVSContainerWorker extends KVSServerFailure {}