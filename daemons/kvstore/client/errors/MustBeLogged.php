<?php
namespace wfw\daemons\kvstore\client\errors;

/**
 *  Le client n'est pas connecté est a tenté d'envoyer une requête sur le serveur.
 */
final class MustBeLogged extends KVSClientFailure {}