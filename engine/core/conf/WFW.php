<?php

namespace wfw\engine\core\conf;

use wfw\engine\core\security\ISecurityPolicy;
use wfw\engine\core\security\WFWDefaultSecurityPolicy;

/**
 * Base descriptor
 */
final class WFW extends ModuleDescriptor implements IAppModulesCollector, ISecurityPolicy {
	private static $_modules = [];
	private static $_collected = false;

	/**
	 * @return array
	 */
	public static function confs(): array {
		$projectFolder = self::root();
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
	 * @return string
	 */
	public static function root(): string {
		return dirname(__DIR__,3);
	}

	/**
	 * @param array $langs
	 * @return array
	 */
	public static function langs(?array $langs=null): array {
		$langs = is_array($langs) ? array_flip($langs) : null;
		$files = [];
		$projectFolder = self::root();
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
			self::getSecurityPolicies()
		);
	}

	/**
	 * @return array
	 */
	public static function di(): array {
		$di = array_map(function($module){
			/** @var IModuleDescriptor $module */
			return $module::di();
		},self::$_modules);
		if(count($di) > 1) return array_merge(...$di);
		else return $di;
	}

	/**
	 * @return array
	 */
	public static function commandHandlers(): array {
		$projectFolder = self::root();
		$siteFile = "$projectFolder/site/config/site.command.handlers.php";
		$engineFile = "$projectFolder/engine/config/default.command.handlers.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return self::mergeConstructors(...array_merge(
			array_merge(
				[$engine],
				array_map(function($module){
					/** @var IModuleDescriptor $module */
					return $module::commandHandlers();
				},self::$_modules)),
			[$site]
		));
	}

	/**
	 * @return array
	 */
	public static function domainEventListeners(): array {
		$projectFolder = self::root();
		$siteFile = "$projectFolder/site/config/site.domain_events.listeners.php";
		$engineFile = "$projectFolder/engine/config/default.domain_events.listeners.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return self::mergeConstructors(...array_merge(
			array_merge(
				[$engine],
				array_map(function($module){
					/** @var IModuleDescriptor $module */
					return $module::domainEventListeners();
					},
					self::$_modules
				)
			),
			[$site]
		));
	}

	/**
	 * @return array
	 */
	public static function models(): array {
		$projectFolder = self::root();
		$siteFile = "$projectFolder/site/config/site.models.php";
		$engineFile = "$projectFolder/engine/config/default.models.php";
		$site = [];
		$engine = [];
		if(file_exists($siteFile)) $site = require $siteFile;
		if(file_exists($engineFile)) $engine = require $engineFile;
		return self::mergeConstructors(...array_merge(
			array_merge(
				[$engine],
				array_map(function($module){
					/** @var IModuleDescriptor $module */
					return $module::models();
				},
					self::$_modules
				)
			),
			[$site]
		));
	}

	/**
	 * @param string ...$modules List of class that implements IModuleDescriptor interface.
	 */
	public static function registerModules(string ...$modules): void {
		foreach($modules as $module){
			if(is_a($module,IModuleDescriptor::class,true)){
				self::$_modules[] = $module;
			}else throw new \InvalidArgumentException(
				"$module doesn't implements ".IModuleDescriptor::class
			);
		}
	}

	/**
	 * The default site descriptor is automaticaly appened to the module list if no
	 * wfw\\site\\config\\SiteDescriptor class exists.
	 *
	 * @param string $fileName  Name of register files for modules
	 *                          modules.
	 */
	public static function collectModules(string $fileName = "module.registration.php"): void {
		if(!self::$_collected){
			$dir = self::root();
			$out = [];
			exec("find \"$dir\" -type f -name \"*$fileName\" | sort",$out);
			foreach($out as $file) require_once $file;
			self::$_modules = array_merge(
				self::$_modules,
				[ class_exists($c = "wfw\\site\\config\\SiteDescriptor") ? $c : DefaultSiteDescriptor::class ]
			);
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

	/**
	 * @param array ...$init
	 * @return array
	 */
	private static function mergeConstructors(array ...$init):array{
		$res = [];
		foreach($init as $initArray){
			foreach($initArray as $key => $constructor){
				if(!isset($res[$key])) $res[$key] = $constructor;
				else{
					foreach($constructor as $index => $param){
						if(isset($res[$key][$index]) && is_array($res[$key][$index]) && is_array($param)){
							$res[$key][$index] = array_merge_recursive(
								$res[$key][$index],
								$param
							);
						}else $res[$key][$index] = $param;
					}
				}
			}
		}
		return $res;
	}

	/**
	 * @param array|null $accessRules Access rules to replace the default security policy
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(?array $accessRules=null): array {
		return self::mergeConstructors(
			$accessRules ?? WFWDefaultSecurityPolicy::accessPolicy(),
			...array_map(
				function($securityPolicy){
					/** @var ISecurityPolicy $securityPolicy */
					return $securityPolicy::accessPolicy();
				}, self::getSecurityPolicies())
		);
	}

	/**
	 * @return array
	 */
	private static function getSecurityPolicies():array{
		$policies = array_map(function($module){
			/** @var IModuleDescriptor $module */
			return $module::securityPolicies();
		},self::$_modules);
		if(count($policies) > 1) return array_unique(array_merge(...$policies));
		else return [];
	}

	/**
	 * @param array|null $commands Command rules to replace the default security policy
	 * @return array [CommandAccessRuleClass => params]
	 */
	public static function commandsPolicy(?array $commands = null): array {
		return self::mergeConstructors(
			$commands ?? WFWDefaultSecurityPolicy::commandsPolicy(),
			 ...array_map(
				function($securityPolicy){
					/** @var ISecurityPolicy $securityPolicy */
					return $securityPolicy::commandsPolicy();
				},
				self::getSecurityPolicies()
			)
		);
	}

	/**
	 * @param array|null $queries Queries rules to replace the default security policy
	 * @return array [QueryAccessRuleClass => params]
	 */
	public static function queriesPolicy(?array $queries = null): array {
		return self::mergeConstructors(
			$queries ?? WFWDefaultSecurityPolicy::queriesPolicy(),
			...array_map(
				function($securityPolicy){
					/** @var ISecurityPolicy $securityPolicy */
					return $securityPolicy::queriesPolicy();
				},
				self::getSecurityPolicies()
			)
		);
	}

	/**
	 * @param array|null $hooks
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy(?array $hooks = null): array {
		return self::mergeConstructors(
			$hooks ?? WFWDefaultSecurityPolicy::hooksPolicy(),
			...array_map(
				function($securityPolicy){
					/** @var ISecurityPolicy $securityPolicy */
					return $securityPolicy::hooksPolicy();
				},
				self::getSecurityPolicies()
			)
		);
	}

	/**
	 * @param string|null $from source to resolve from
	 * @return string[] List of paths that must be cleaned
	 * @throws \ReflectionException
	 */
	public static function cleanablePathsForUpdate(string $from=null): array{
		return array_merge(
			self::subdirectories("cli/backup",["config"],$from),
			self::subdirectories("cli/installer",[],$from),
			self::subdirectories("cli/tester",["config"],$from),
			self::subdirectories("cli/webroot",[],$from),
			self::subdirectories("cli/wfw",["global.db.json","config","a2.d"],$from),
			self::subdirectories("cli/zipCode",[],$from),
			self::subdirectories("daemons/kvstore",["data","server"],$from),
			self::subdirectories("daemons/kvstore/server",["config"],$from),
			self::subdirectories("daemons/modelSupervisor",["data","server"],$from),
			self::subdirectories("daemons/modelSupervisor/server",["config"],$from),
			self::subdirectories("daemons/sctl",["data","config"],$from),
			self::subdirectories("daemons/rts",["data","server"],$from),
			self::subdirectories("daemons/rts/server",["config"],$from),
			self::subdirectories("daemons/multiProcWorker",[],$from),
			self::subdirectories("engine",["config"],$from),
			self::subdirectories("engine/config",["conf.json"],$from),
			self::subdirectories("docs",[],$from),
			self::subdirectories("tests",[],$from)
		);
	}

	/**
	 * @return array
	 */
	public static function cleanablePaths(): array {
		return array_merge(
			[],
			...array_map(function($module){
				/** @var IModuleDescriptor $module */
				return $module::cleanablePaths();
			},self::$_modules)
		);
	}
}