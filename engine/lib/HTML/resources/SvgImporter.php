<?php
namespace wfw\engine\lib\HTML\resources;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Permet d'importer des fichiers svg.
 * Gère la duplication d'identifiants.
 */
final class SvgImporter {
	private const CACHE_KEY = "wfw/view/svgImporter/";

	/** @var string $_basePath */
	private $_basePath;

	/** @var ICacheSystem $_cache */
	private $_cache;

	/** @var string[] files its (index on file path) */
	private static $_hits=[];

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
		if(!isset(self::$_hits[$path])) self::$_hits[$path] = 0;
		else self::$_hits[$path]++;
		$res = $this->_cache->get(self::CACHE_KEY.$path.self::$_hits[$path]);
		if(is_null($res)){
			ob_start();
			include($path);
			$res = $this->randomizeIDS(ob_get_clean());
			$this->_cache->set(self::CACHE_KEY.$path.self::$_hits[$path],$res);
		}
		return $res;
	}

	/**
	 * Randomize all svg ids.
	 * @param string $content content to randomize
	 * @return string
	 */
	private function randomizeIDS(string $content):string{
		$ids = [];
		preg_match_all('/id=\"(.*)\"/iU',$content,$ids, PREG_SET_ORDER);
		$ids = array_map(function($id){return "/($id[1])/";},$ids);
		$subs = [];
		foreach($ids as $id){
			$subs[]=str_replace("-","",new UUID(UUID::V4));
		}
		$content = preg_replace("/(<!.*>)/","",$content);
		$content = preg_replace("/(<\?xml.*\?>)/","",$content);
		return preg_replace($ids,$subs,$content);
	}
}