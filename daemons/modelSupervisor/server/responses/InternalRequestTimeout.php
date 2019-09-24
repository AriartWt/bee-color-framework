<?php
namespace wfw\daemons\modelSupervisor\server\responses;

/**
 * Le délais d'attente de la requête est dépassé. Impossible de joindre l'un des components.
 */
final class InternalRequestTimeout extends RequestError {}