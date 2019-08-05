<?php

namespace wfw\engine\core;

use wfw\engine\core\app\WebApp;

/**
 * Main class called by the entry point (engine/webroot/indexp.php) if no wfw\site\core\Main class
 * is defined.
 */
class Main {
	/**
	 *  Called by the index.php file that recieve the request.
	 * @param array $args Arguments
	 */
	public function __construct(array $args=[]){
		$contextInfos = (require dirname(__DIR__,2)."/site/config/site.context.php")($args);
		new WebApp(new $contextInfos["class"](...($contextInfos["args"] ?? [])));
	}
}