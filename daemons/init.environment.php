<?php
require_once dirname(dirname(__FILE__))."/engine/webroot/init.environment.php";

(new \wfw\engine\core\errors\handlers\DefaultErrorHandler(true))->handle();