<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/01/18
 * Time: 04:35
 */

namespace wfw\daemons\kvstore\client\errors;

/**
 *  Le client n'est pas connecté est a tenté d'envoyer une requête sur le serveur.
 */
final class MustBeLogged extends KVSClientFailure {}