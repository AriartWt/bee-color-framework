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
	 * @return array
	 */
	public static function confs(): array {
		$projectFolder = dirname(__DIR__,3);
		$engineFile = ["$projectFolder/engine/config/conf.json"];
		$siteFile = ["$projectFolder/site/config/conf.json"];
		return array_merge(
			array_merge(
				$engineFile,
				...array_map(function($module){
						/** @var IModuleDescriptor $module */
						return $module::confs();
					},
					self::$_modules
				)
			),
			$siteFile
		);
	}

	/**
	 * @param array $langs
	 * @return array
	 */
	public static function langs(?array $langs=null): array {
		$langs = is_array($langs) ? array_flip($langs) : null;
		$files = [];
		$projectFolder = dirname(__DIR__,3);
		foreach(["engine","site"] as $folder){
			$files[$folder] = [];
			exec(
				"find \"$projectFolder/$folder\" -name *.lang.json -type f | sort",
				$files[$folder]
			);
		}
		$indexedFiles = [];
		$files = array_merge(
			array_merge($files["engine"],...array_map(
				function($module){
					/** @var IModuleDescriptor $module */
					return $module::langs();
				},self::$_modules
			)),
			$files["site"]
		);
		array_filter(
			$files,
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
	 * @return array
	 */
	public static function di(): array {
		return array_merge_recursive(...array_map(function($module){
			/** @var IModuleDescriptor $module */
			return $module::di();
		},self::$_modules));
	}

	/**
	 * @return array
	 */
	public static function commandHandlers(): array {
		$projectFolder = dirname(__DIR__,3);
		$siteFile = "$projectFolder/site/config/site.command.handlers.php";
		$engineFile = "$projectFolder/engine/config/default.command.handlers.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return array_merge_recursive(
			array_merge_recursive(
				$engine,
				...array_map(function($module){
				/** @var IModuleDescriptor $module */
				return $module::domainEventListeners();
			},self::$_modules)),
			$site
		);
	}

	/**
	 * @return array
	 */
	public static function domainEventListeners(): array {
		$projectFolder = dirname(__DIR__,3);
		$siteFile = "$projectFolder/site/config/site.domain_events.listeners.php";
		$engineFile = "$projectFolder/engine/config/default.domain_events.listeners.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return array_merge_recursive(
			array_merge_recursive(
				$engine,
				...array_map(function($module){
				/** @var IModuleDescriptor $module */
				return $module::domainEventListeners();
			},self::$_modules)),
			$site
		);
	}

	/**
	 * @return array
	 */
	public static function models(): array {
		$projectFolder = dirname(__DIR__,3);
		$siteFile = "$projectFolder/site/config/site.models.php";
		$engineFile = "$projectFolder/engine/config/default.models.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return array_merge(
			array_merge(
				$engine,
				...array_map(function($module){
				/** @var IModuleDescriptor $module */
				return $module::models();
			},self::$_modules)),
			$site
		);
	}

	/**
	 * @param string ...$modules List of class that implements IModuleDescriptor interface.
	 */
	public static function registerModules(string ...$modules): void {
		foreach($modules as $module){
			if(is_a($module,IModuleDescriptor::class)){
				/** @var IModuleDescriptor $module */
				self::$_modules[(string)$module] = $module::root();
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
			exec("find \"$dir\" -type f -name \"*$fileName\" | sort",$out);
			foreach($out as $file) require_once $file;
		}
	}

	/**
	 * @return string[] Registered modules descriptor classes.
	 */
	public static function modules(): array {
		return self::$_modules;
	}

	/**
	 * @param string[] $modules List of class modules saved in cache. Must prevent the collectModules
	 *                          method to perform a slow task to retrieve all modules.
	 *                          [Class => dirname]
	 */
	public static function restoreModulesFromCache(array $modules): void {
		self::$_modules = $modules;
		self::$_collected = true;
	}
}