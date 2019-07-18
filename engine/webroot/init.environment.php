<?php
	define("START_TIME",microtime(true));

	define('DS',DIRECTORY_SEPARATOR);
	define('WEBROOT', dirname(__FILE__)); /**< path to engine/webroot */
	define("ENGINE",dirname(WEBROOT));
	define("ROOT",dirname(ENGINE));/**<  path to project root */
	define("SITE",ROOT.DS."site");/**<  path to site/ */
	define("DAEMONS",ROOT.DS."daemons");/**<  path to daemons/ */
	define("CLI",ROOT.DS."cli");
	define("WWW",dirname(ROOT));/**<  path to public folder */
	define('CORE', ENGINE.DS.'core');/**< path to egine/core */
	define('BASE_URL',
		((dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))!=="\\")
			?dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))
			:false)
	);
	define('PLUGINS',ENGINE.DS.'plugin'.DS);/**  path to website/engine/plugin */

	mb_internal_encoding('UTF-8');
	ignore_user_abort(true);
	ini_set("session.serialize_handler","php_serialize");

	setlocale(LC_CTYPE, 'fr_FR','fra');
	date_default_timezone_set('Europe/Paris');

	require CORE.DS.'Autoloader.php';
	use wfw\Autoloader;
	Autoloader::register();

	use wfw\engine\lib\debug\Debuger;

	/**
	 * Define a global function to make debug easier.
	 * @param $var var to debug
	 */
	function debug($var){
		Debuger::get()->debug($var);
	}