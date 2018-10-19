<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/01/18
 * Time: 03:40
 */

namespace wfw\daemons\modelSupervisor\server\errors;

/**
 * Le serveur a reçu un signal pour sa fermeture. Cette exception n'est pas lancée pour un SIGKILL (9)
 */
final class ExternalShutdown extends MSServerFailure {}