<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/01/18
 * Time: 03:05
 */

namespace wfw\daemons\kvstore\socket\data\errors;

use wfw\daemons\kvstore\errors\KVSFailure;

/**
 * Une erreur est survenue lors de la linéarisation de la requête/réponse.
 */
final class KVSDataLinearisationFailure extends KVSFailure {}