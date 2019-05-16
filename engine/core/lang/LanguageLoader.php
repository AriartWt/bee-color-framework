<?php
namespace wfw\engine\core\lang;

use stdClass;
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\system\filesystem\json\JSONFile;

/**
 * Charge des fichiers de langue au format JSON
 */
final class LanguageLoader implements ILanguageLoader {
	/** @var ICacheSystem $_cache */
	private $_cache;

	/**
	 * LanguageLoader constructor.
	 *
	 * @param ICacheSystem $cacheSystem
	 */
	public function __construct(ICacheSystem $cacheSystem) {
		$this->_cache = $cacheSystem;
	}

	/**
	 * @param string[] $paths Chemin d'accès au fichier de langue à charger.
	 * @return IStrRepository
	 * @throws \Exception
	 */
	public function load(string ...$paths): IStrRepository {
		$key = self::class."/".json_encode($paths);
		if($this->_cache->contains($key)) return $this->_cache->get($key);
		$data = new stdClass();
		$operator = new StdClassOperator($data);
		foreach($paths as $path){
			$operator->mergeStdClass((new JSONFile($path))->read());
		}
		$this->_cache->set($key,$res = new StrRepository($data));
		return $res;
	}
}