<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/01/18
 * Time: 03:29
 */

namespace wfw\daemons\kvstore\server\errors;

/**
 * @brief Un signal handler a déclenché l'extinction du server. Cette erreur n'est pas lancée pour le signal SIGKILL (9)
 */
final class ExternalShutdown extends KVSServerFailure {}