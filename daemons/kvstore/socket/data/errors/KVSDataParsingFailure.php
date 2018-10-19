<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/01/18
 * Time: 02:34
 */

namespace wfw\daemons\kvstore\socket\data\errors;

use wfw\daemons\kvstore\errors\KVSFailure;

/**
 * Le parsing d'une requête ou d'une réponse a échoué.
 */
final class KVSDataParsingFailure extends KVSFailure {}