<?php
namespace wfw\daemons\modelSupervisor\server\errors;

/**
 * Le serveur a reçu un signal pour sa fermeture. Cette exception n'est pas lancée pour un SIGKILL (9)
 */
final class ExternalShutdown extends MSServerFailure {}