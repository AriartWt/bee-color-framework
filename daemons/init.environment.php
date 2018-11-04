<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."engine".DIRECTORY_SEPARATOR."webroot".DIRECTORY_SEPARATOR."init.environment.php";

(new \wfw\engine\core\errors\handlers\DefaultErrorHandler(true))->handle();