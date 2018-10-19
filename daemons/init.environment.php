<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/12/17
 * Time: 01:14
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."engine".DIRECTORY_SEPARATOR."webroot".DIRECTORY_SEPARATOR."init.environment.php";

(new \wfw\engine\core\errors\handlers\DefaultErrorHandler(true))->handle();