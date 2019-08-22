<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/08/18
 * Time: 09:05
 */

namespace wfw\daemons\rts\server\errors;
use wfw\daemons\rts\errors\RTSFailure;

/**
 * Le serveur ne peux plus créer de workers supplémentaires
 */
final class MaxWorkerLimitReached extends RTSFailure{}