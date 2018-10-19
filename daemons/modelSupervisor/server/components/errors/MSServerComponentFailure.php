<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/01/18
 * Time: 02:15
 */

namespace wfw\daemons\modelSupervisor\server\components\errors;

use wfw\daemons\modelSupervisor\server\errors\MSServerFailure;

/**
 * @brief Exception levée par un composant du MSServer
 */
class MSServerComponentFailure extends MSServerFailure {}