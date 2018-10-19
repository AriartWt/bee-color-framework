<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/01/18
 * Time: 06:31
 */

namespace wfw\daemons\modelSupervisor\client\errors;

use wfw\daemons\modelSupervisor\errors\MSFailure;

/**
 * @brief Exception levée par un client MSServer.
 */
class MSClientFailure extends MSFailure {}