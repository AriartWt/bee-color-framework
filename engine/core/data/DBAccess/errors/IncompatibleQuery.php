<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/11/17
 * Time: 05:40
 */

namespace wfw\engine\core\data\DBAccess\errors;

/**
 *  Levée lorsqu'une SQLQuery n'est pas compatible avec l'interface DBAccess
 */
class IncompatibleQuery extends DBAccessFailure {}