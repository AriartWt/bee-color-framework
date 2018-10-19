<?php
/**
*	Premier fichier chargé par l'application.
 *  Invoque la classe wfw\site\core\Main.
**/
    require_once "./init.environment.php";

	new \wfw\site\core\Main([
	    "globals" => [
	        "_GET" => &$_GET,
            "_POST" => &$_POST,
            "_FILES" => &$_FILES,
            "_SERVER" => &$_SERVER
        ]
    ]);