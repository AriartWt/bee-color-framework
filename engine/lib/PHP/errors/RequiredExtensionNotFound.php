<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 02:30
 */

namespace wfw\engine\lib\PHP\errors;

/**
 *  Exception levée lorsqu'une extension nécessaire n'es tpas chargée
 */
class RequiredExtensionNotFound extends \Exception {}