<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 03:48
 */

namespace wfw\engine\lib\PHP\errors;

/**
 *  Levée lorsqu'une méthode est appelée sur un objet qui ne la supporte pas ou d'une manière incorrecte.
 */
class IllegalInvocation extends \Exception {}