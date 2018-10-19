<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/02/18
 * Time: 10:07
 */

namespace wfw\engine\core\action\errors;

/**
 * L'ActionRouter n'a pas été en mesure de trouver le handler d'une action.
 */
final class ActionHandlerNotFound extends ActionResolutionFailure {}