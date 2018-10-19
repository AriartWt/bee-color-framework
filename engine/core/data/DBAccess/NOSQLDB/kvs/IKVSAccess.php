<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/02/18
 * Time: 09:26
 */

namespace wfw\engine\core\data\DBAccess\NOSQLDB\kvs;

use wfw\daemons\kvstore\client\IKVSClient;

/**
 * Interface d'un acces KVS
 */
interface IKVSAccess extends IKVSClient {}