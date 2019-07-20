<?php


require_once dirname(__FILE__,2)."/engine/webroot/init.environment.php";

(new \wfw\engine\core\errors\handlers\DefaultErrorHandler(false))->handle();