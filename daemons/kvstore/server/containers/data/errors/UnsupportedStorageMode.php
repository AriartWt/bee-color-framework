<?php
namespace wfw\daemons\kvstore\server\containers\data\errors;

use wfw\daemons\kvstore\server\containers\errors\KVSContainerFailure;

/**
 *  Le mode de stockage de données n'est pas supporté.
 */
final class UnsupportedStorageMode extends KVSContainerFailure{}