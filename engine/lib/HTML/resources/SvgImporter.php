<?php
namespace wfw\engine\lib\HTML\resources;

use wfw\engine\core\cache\ICacheSystem;

/**
 * Permet d'importer des fichiers svg
 */
final class SvgImporter {
	private const CACHE_KEY = "wfw/view/svgImporter/";

	/** @var string $_basePath */
	private $_basePath;

	/** @var ICacheSystem $_cache */
	private $_cache;

	/**
	 * SvgImporter constructor.
	 *
	 * @param string       $basePath Chemin de base jusqu'au SVG à importer.
	 * @param ICacheSystem $cacheSystem Systeme de cache.
	 */
	public function __construct(string $basePath,ICacheSystem $cacheSystem) {
		$this->_basePath = $basePath;
		$this->_cache = $cacheSystem;
	}

	/**
	 * @param string $svgName Chemin relatif résolu à partir du basePath de l'instance courante.
	 * @param bool   $absolutePath Permet d'importer en tant que chemin absolu
	 * @return string
	 */
	public function import(string $svgName,bool $absolutePath = false):string{
		$path = $absolutePath?$svgName:$this->_basePath."/".$svgName;
		$res = $this->_cache->get(self::CACHE_KEY.$path);
		if(is_null($res)){
			ob_start();
			include($path);
			$res = ob_get_clean();
			$this->_cache->set(self::CACHE_KEY.$path,$res);
		}
		return $res;
	}
}