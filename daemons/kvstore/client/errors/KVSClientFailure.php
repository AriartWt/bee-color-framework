<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/01/18
 * Time: 03:57
 */

namespace wfw\daemons\kvstore\client\errors;

use wfw\daemons\kvstore\errors\KVSFailure;

/**
 *  Exception levée par un client du KVS
 */
class KVSClientFailure extends KVSFailure {}