<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/01/18
 * Time: 05:10
 */

namespace wfw\daemons\kvstore\server\containers\errors;

use wfw\daemons\kvstore\server\errors\KVSServerFailure;

/**
 *  Erreur levée par un ContainerWorker
 */
class KVSContainerFailure extends KVSServerFailure {}