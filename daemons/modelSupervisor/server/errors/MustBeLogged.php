<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/01/18
 * Time: 01:31
 */

namespace wfw\daemons\modelSupervisor\server\errors;

/**
 *  l'action requiert d'être connecté.
 */
final class MustBeLogged extends AccessDenied {}