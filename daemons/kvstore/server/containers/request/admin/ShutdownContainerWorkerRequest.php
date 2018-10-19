<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/01/18
 * Time: 04:54
 */

namespace wfw\daemons\kvstore\server\containers\request\admin;

use wfw\daemons\kvstore\server\requests\AbstractKVSRequest;

/**
 *  Demande au worker d'arrêter son execution.
 */
final class ShutdownContainerWorkerRequest extends AbstractKVSRequest implements IKVSAdminContainerRequest {}