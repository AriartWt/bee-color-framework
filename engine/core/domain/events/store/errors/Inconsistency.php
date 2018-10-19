<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 22/11/17
 * Time: 05:27
 */

namespace wfw\engine\core\domain\events\store\errors;

/**
 *  Levée lorsqu'une incohérence est détéctée par l'event store
 */
class Inconsistency extends \Exception {}