<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/01/18
 * Time: 06:35
 */

namespace wfw\daemons\modelSupervisor\client\errors;

/**
 * Le client a tenté de se connecter une seconde fois alors que sa session est toujours active.
 */
final class AlreadyLogged extends MSClientFailure {}