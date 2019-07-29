<?php
namespace wfw\site\core;

use wfw\engine\core\app\WebApp;

/**
 *  Entry point
 */
final class Main {
	/**
	 *  Called by the index.php file that recieve the request.
	 * @param array $args Arguments
	 */
	public function __construct(array $args=[]){
		$contextInfos = (require dirname(__DIR__)."/config/site.context.php")($args);
		new WebApp(new $contextInfos["class"](...($contextInfos["args"] ?? [])));
	}
}