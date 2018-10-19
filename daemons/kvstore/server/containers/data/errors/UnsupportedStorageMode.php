<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 07:35
 */

namespace wfw\daemons\kvstore\server\containers\data\errors;

use wfw\daemons\kvstore\server\containers\errors\KVSContainerFailure;

/**
 *  Le mode de stockage de données n'est pas supporté.
 */
final class UnsupportedStorageMode extends KVSContainerFailure{}