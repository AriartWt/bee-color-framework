<?php
namespace wfw\daemons\kvstore\server\containers\data\errors;

use wfw\daemons\kvstore\server\containers\errors\KVSContainerFailure;

/**
 *  La clé de stockage n'est pas valide !
 */
final class InvalidKeySupplied extends KVSContainerFailure {}