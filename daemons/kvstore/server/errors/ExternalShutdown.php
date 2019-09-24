<?php
namespace wfw\daemons\kvstore\server\errors;

/**
 * @brief Un signal handler a déclenché l'extinction du server. Cette erreur n'est pas lancée pour le signal SIGKILL (9)
 */
final class ExternalShutdown extends KVSServerFailure {}