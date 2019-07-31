<?php
	define("START_TIME",microtime(true));

	mb_internal_encoding('UTF-8');
	ignore_user_abort(true);
	ini_set("session.serialize_handler","php_serialize");

	setlocale(LC_CTYPE, 'fr_FR','fra');
	date_default_timezone_set('Europe/Paris');

ini_set('xdebug.var_display_max_depth', '10');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');

	require dirname(__DIR__).'/core/Autoloader.php';
	use wfw\Autoloader;
	(new Autoloader())->register();

	use wfw\engine\lib\debug\Debuger;

	/**
	 * Define a global function to make debug easier.
	 * @param $var var to debug
	 */
	function debug($var){
		Debuger::get()->debug($var);
	}