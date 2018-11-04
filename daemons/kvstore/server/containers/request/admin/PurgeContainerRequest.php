<?php
namespace wfw\daemons\kvstore\server\containers\request\admin;

use wfw\daemons\kvstore\server\requests\AbstractKVSRequest;

/**
 *  Supprime toutes les données du container.
 */
final class PurgeContainerRequest extends AbstractKVSRequest implements IKVSAdminContainerRequest {}