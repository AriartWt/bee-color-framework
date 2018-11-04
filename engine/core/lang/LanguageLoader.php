<?php
namespace wfw\engine\core\lang;

use stdClass;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\system\filesystem\json\JSONFile;

/**
 * Charge des fichiers de langue au format JSON
 */
final class LanguageLoader implements ILanguageLoader {
	/**
	 * @param string[] $paths Chemin d'accès au fichier de langue à charger.
	 * @return IStrRepository
	 * @throws \Exception
	 */
	public function load(string ...$paths): IStrRepository {
		//todo : cache files
		$data = new stdClass();
		$operator = new StdClassOperator($data);
		foreach($paths as $path){
			$operator->mergeStdClass((new JSONFile($path))->read());
		}
		return new StrRepository($data);
	}
}