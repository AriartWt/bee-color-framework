<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 03/10/17
 * Time: 01:03
 */

namespace wfw\engine\lib\errors;

use Exception;

/**
 *  Excetption levée lorsqu'un fichier n'est pas trouvé
 */
class FileNotFound extends Exception {}