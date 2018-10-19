<?php
namespace wfw\site\core;

use wfw\engine\core\app\WebApp;

/**
 *  Point d'entrée
 */
final class Main {
	/**
	 *  Constructeur. Appelé par le fichier index.php recevant la requête
	 * @param array $args Arguments
	 */
	public function __construct(array $args=[]){
		$contextInfos = (require SITE."/config/site.context.php")($args);
		new WebApp(new $contextInfos["class"](...$contextInfos["args"]));
	}
}