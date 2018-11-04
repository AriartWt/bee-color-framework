<?php
namespace wfw\engine\core\conf\io\adapters;

use stdClass;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\conf\io\adapters\errors\ConfFileFailure;
use wfw\engine\core\conf\io\IConfIOAdapter;
use wfw\engine\lib\PHP\system\filesystem\json\JSONFile;

/**
 *  Permet de lire des fichiers de configuration au format JSON
 */
final class JSONConfIOAdapter implements IConfIOAdapter {
	/**
	 * @brief Parse un fichier JSON et retourne le résultat sous la forme d'un stdClass.
	 *
	 * @param string $path Chemin complet d'accés au fichier
	 *
	 * @return stdClass Fichier parsé
	 * @throws ConfFileFailure Si le fichier n'existe pas
	 * @throws \Exception                Si le fichier n'existe pas (JSONFile)
	 */
	public function parse(string $path): stdClass {
		try{
			$file = new JSONFile($path);
			$conf = $file->read();
			if($conf instanceof stdClass){
				return $conf;
			}else{
				return new stdClass();
			}
		}catch(\Exception $e) {
			throw new ConfFileFailure($e->getMessage(),$e->getCode(),$e);
		}
	}

	/**
	 * @brief Sauvegarde la configuration courante dans un fichier.
	 * @param IConf $conf Confs à sauvegarder
	 */
	public function save(IConf $conf): void {
		if(method_exists($conf,'getConfPath')){
			$file = new JSONFile($conf->getConfPath());
			$file->write($conf->getRawConf());
		}else{
			throw new \InvalidArgumentException("JSONConfIO only supports conf that implements a getConfPath() method");
		}
	}
}