<?php
namespace wfw\engine\core\conf\io;

use stdClass;
use wfw\engine\core\conf\IConf;

/**
 *  Permet de lire un fichier de configuration
 */
interface IConfIOAdapter {
	/**
	 *  Parse un fichier de configuration et retourne un objet stdClass utilisable par la classe Conf
	 * @param string $path Chemin d'accès au fichier (sans l'extension)
	 *
	 * @return stdClass
	 */
	public function parse(string $path):stdClass;

	/**
	 *     Enregistre un fichier de configuration
	 *
	 * @param IConf $conf Objet de configuration à sauvegarder
	 *
	 * @internal param string $path Chemin de sauvegarde du fichier de conf (sans extension)
	 */
	public function save(IConf $conf):void;
}