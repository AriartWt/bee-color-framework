<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/01/18
 * Time: 10:43
 */

namespace wfw\daemons\modelSupervisor\server\responses;

/**
 * Le délais d'attente de la requête est dépassé. Impossible de joindre l'un des components.
 */
final class InternalRequestTimeout extends RequestError {}