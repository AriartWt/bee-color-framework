<?php
namespace wfw\daemons\kvstore\client\errors;

/**
 *  Le client a tenté de se connecter une seconde fois au serveur.
 */
final class AlreadyLogged extends KVSClientFailure {}