<?php
namespace wfw\daemons\kvstore\socket\data\errors;

use wfw\daemons\kvstore\errors\KVSFailure;

/**
 * Le parsing d'une requête ou d'une réponse a échoué.
 */
final class KVSDataParsingFailure extends KVSFailure {}