<?php
namespace wfw\daemons\modelSupervisor\client\errors;

/**
 * Le client a tenté de se connecter une seconde fois alors que sa session est toujours active.
 */
final class AlreadyLogged extends MSClientFailure {}