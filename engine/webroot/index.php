<?php
/**
*	Entry point
**/
require_once "./init.environment.php";

$args = [
	"globals" => [
		"_GET" => &$_GET,
		"_POST" => &$_POST,
		"_FILES" => &$_FILES,
		"_SERVER" => &$_SERVER
	]
];
if(class_exists("wfw\\site\\core\\Main")) new \wfw\site\core\Main($args);
else new \wfw\engine\core\Main($args);