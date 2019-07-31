<?php
/**
*	Entry point
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