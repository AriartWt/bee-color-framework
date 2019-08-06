<?php

namespace wfw\engine\core\conf;

/**
 * Base module descriptor. All module descript may extends this in order to prevent incompatibilities
 * if new methods are added to the interface.
 */
abstract class ModuleDescriptor implements IModuleDescriptor{
	protected static $_confTemplate = "*conf.json";
	protected static $_langTemplate = "*.lang.json";

	 /**
	  * @return array Dependency injection rules to add to the general DI
	  */
	 public static function di(): array {
		 return [];
	 }

	/**
	 * @return string[] List of file confs needed by the module.
	 *                  Confs applyance order is engine -> modules -> project
	 * @throws \ReflectionException
	 */
	 public static function confs(): array {
	 	$confs = [];
	 	$root = static::root();
	 	exec("find \"$root\" -name \"".static::$_confTemplate."\" -type f | sort");
		 return $confs;
	 }

	 /**
	  * @return string[] List of model class that must be registered in site/config/site.models.php
	  */
	 public static function models(): array {
		 return [];
	 }

	 /**
	  * @return string[] List of class that implements ISecurityPolicy and that must be applyed.
	  */
	 public static function securityPolicies(): array {
		 return [];
	 }

	/**
	 * @return string[] List of translation files.
	 * @throws \ReflectionException
	 */
	 public static function langs(): array {
		 $langs = [];
		 $root = static::root();
		 exec("find \"$root\" -name \"".static::$_langTemplate."\" -type f | sort");
		 return $langs;
	 }

	 /**
	  * @return string
	  * @throws \ReflectionException
	  */
	 public static function root():string{
	 	$reflected = new \ReflectionClass(static::class);
	 	return dirname($reflected->getFileName());
	 }

	  /**
	   * @return string[] List of classes that implements IDomainEventListener
	   */
	  public static function domainEventListeners(): array {
		  return [];
	  }

	  /**
	   * @return string[] List of classes that implements ICommandHandlers
	   */
	  public static function commandHandlers(): array {
		  return [];
	  }

	/**
	 * @return string[] List of paths that are cleanable while importing project.
	 */
	  public static function cleanablePaths(): array {
		  return [];
	  }

	/**
	 * Return non-recursive list of all direct subdirectories of one directory
	 *
	 * @param string      $folder  Directory (automaticaly resolved relatively to $from or root)
	 * @param array       $excepts List of directory (relative to $folder) to not include into the result list
	 * @param null|string $from
	 * @return string[] Full path list.
	 * @throws \ReflectionException
	 */
	protected static function subdirectories(string $folder, array $excepts=[], ?string $from=null):array{
		$root = $from ?? static::root();
		if(is_dir("$root/$folder")) return array_map(
			function($path)use($root,$folder){
				if(empty($folder)) return "$root/$path";
				else return "$root/$folder/$path";
			},
			array_diff(
				scandir("$root/$folder"),
				array_merge([".",".."],$excepts)
			)
		);
		else return [];
	}
}