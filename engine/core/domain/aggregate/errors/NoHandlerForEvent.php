<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/11/17
 * Time: 02:36
 */

namespace wfw\engine\core\domain\aggregate\errors;

/**
 *  Exception elvée lorsqu'un aggrégat n'a pas de méthode pour accueillir un événement donné
 */
class NoHandlerForEvent extends \Exception {}