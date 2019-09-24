<?php
namespace wfw\daemons\kvstore\server\errors;

/**
 *  Le container sur laquelle le serveur tente d'executer une requête n'est pas joignable.
 */
final class InactiveKVSContainerWorker extends KVSServerFailure {}