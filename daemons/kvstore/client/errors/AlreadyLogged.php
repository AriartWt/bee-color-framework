<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/01/18
 * Time: 04:34
 */

namespace wfw\daemons\kvstore\client\errors;

/**
 *  Le client a tenté de se connecter une seconde fois au serveur.
 */
final class AlreadyLogged extends KVSClientFailure {}