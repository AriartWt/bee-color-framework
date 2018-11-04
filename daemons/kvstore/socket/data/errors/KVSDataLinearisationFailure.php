<?php
namespace wfw\daemons\kvstore\socket\data\errors;

use wfw\daemons\kvstore\errors\KVSFailure;

/**
 * Une erreur est survenue lors de la linéarisation de la requête/réponse.
 */
final class KVSDataLinearisationFailure extends KVSFailure {}