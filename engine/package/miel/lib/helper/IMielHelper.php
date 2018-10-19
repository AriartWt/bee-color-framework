<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/02/18
 * Time: 08:55
 */

namespace wfw\engine\package\miel\lib\helper;

use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;

/**
 * Helper pour les vues.
 */
interface IMielHelper {
	/**
	 * @param string $key Clé à récupérer
	 * @return string attribut html à placer dans les balises.
	 */
	public function getHTMLForKey(string $key):string;
}