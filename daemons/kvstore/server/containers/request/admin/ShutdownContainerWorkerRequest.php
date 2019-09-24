<?php
namespace wfw\daemons\kvstore\server\containers\request\admin;

use wfw\daemons\kvstore\server\requests\AbstractKVSRequest;

/**
 *  Demande au worker d'arrêter son execution.
 */
final class ShutdownContainerWorkerRequest extends AbstractKVSRequest implements IKVSAdminContainerRequest {}