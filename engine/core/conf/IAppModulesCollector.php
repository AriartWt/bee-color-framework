<?php

namespace wfw\engine\core\conf;

/**
 * Allow to search for modules to collect.
 */
interface IAppModulesCollector {
	/**
	 * @return string[] [Class => dirname ] Registered modules descriptor classes
	 *                  associated to the dirname of the module
	 */
	public static function modules():array;

	/**
	 * @param string $fileName Name of register files for modules
	 */
	public static function collectModules(string $fileName = "module.registration.php"):void;

	/**
	 * @param string ...$modules List of class that implements IModuleDescriptor interface.
	 */
	public static function registerModules(string ...$modules):void;

	/**
	 * @param string[] $modules List of class modules saved in cache. Must prevent the collectModules
	 *                          method to perform a slow task to retrieve all modules.
	 *                          [Class => dirname]
	 */
	public static function restoreModulesFromCache(array $modules):void;
}