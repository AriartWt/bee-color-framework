<?php

namespace wfw\engine\core\conf;

use wfw\engine\core\security\WFWDefaultSecurityPolicy;

/**
 * Base descriptor
 */
final class WFWModulesCollector extends ModuleDescriptor implements IAppModulesCollector {
	private static $_modules = [];
	private static $_collected = false;

	/**
	 * @param array $langs
	 * @return array
	 */
	public static function langs(?array $langs=null): array {
		$langs = is_array($langs) ? array_flip($langs) : null;
		$engineFiles = array_diff(
			scandir(dirname(__DIR__,2)."/config/lang"),
			['.','..']
		);
		$siteFiles = [];
		if(is_dir(dirname(__DIR__,3).'/site/config/lang')){
			$siteFiles = array_diff(
				scandir(dirname(__DIR__,3)."/site/config/lang"),
				['.','..']
			);
		}
		$indexedFiles = [];
		array_filter(
			array_merge(
				array_map(function($path){
					return dirname(__DIR__,2)."/config/lang/$path";
				},$engineFiles),
				array_map(function($path){
					return dirname(__DIR__,3)."/site/config/lang/$path";
				},$siteFiles)
			),
			function($path) use ($langs,&$indexedFiles):bool{
				$index = explode(".",basename($path))[0];
				if(is_null($langs) || isset($langs[$index])){
					if(!isset($indexedFiles[$index])) $indexedFiles[$index] = [];
					$indexedFiles[$index][] = $path;
					return true;
				}
				return false;
		});
		return $indexedFiles;
	}

	/**
	 * @return array
	 */
	public static function securityPolicies(): array {
		return array_merge(
			[ WFWDefaultSecurityPolicy::class ],
			...array_map(
				function($module){
					/** @var IModuleDescriptor $module */
					return $module::securityPolicies();
				},
				self::$_modules
			)
		);
	}

	/**
	 * @param string ...$modules List of class that implements IModuleDescriptor interface.
	 */
	public static function registerModules(string ...$modules): void {
		foreach($modules as $module){
			if(is_a($module,IModuleDescriptor::class)){
				self::$_modules[] = $module;
			}else throw new \InvalidArgumentException(
				"$module doesn't implements ".IModuleDescriptor::class
			);
		}
	}

	/**
	 * @param string $fileName  Name of register files for modules
	 *                          modules.
	 */
	public static function collectModules(string $fileName = "module.registration.php"): void {
		if(!self::$_collected){
			$dir = dirname(__DIR__,3);
			$out = [];
			exec("find \"$dir\" -f -name \"$fileName\"",$out);
			foreach($out as $file) require_once $file;
		}
	}

	/**
	 * @return string[] Registered modules descriptor classes.
	 */
	public static function modules(): array {
		return self::$_modules;
	}
}