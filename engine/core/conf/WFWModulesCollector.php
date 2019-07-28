<?php

namespace wfw\engine\core\conf;

use wfw\engine\core\security\WFWDefaultSecurityPolicy;

/**
 * Base descriptor
 */
final class WFWModulesCollector extends ModuleDescriptor implements IAppModulesCollector {
	private static $_modules = [];

	/**
	 * @return array
	 */
	public static function securityPolicies(): array {
		return [ WFWDefaultSecurityPolicy::class ];
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
	 * @param string $fileName Name of register files for modules
	 */
	public static function collectModules(string $fileName = "module.registration.php"): void {
		$dir = dirname(__DIR__,3);
		$out = [];
		exec("find \"$dir\" -f -name \"$fileName\"",$out);
		foreach($out as $file) require_once $file;
	}

	/**
	 * @return string[] Registered modules descriptor classes.
	 */
	public static function modules(): array {
		return self::$_modules;
	}
}