<?php

namespace wfw\engine\core\conf;

/**
 * Base module descriptor. All module descript may extends this in order to prevent incompatibilities
 * if new methods are added to the interface.
 */
 abstract class ModuleDescriptor implements IModuleDescriptor{

	 /**
	  * @return array Dependency injection rules to add to the general DI
	  */
	 public static function di(): array {
		 return [];
	 }

	 /**
	  * @return string[] List of file confs needed by the module.
	  *                  Confs applyance order is engine -> modules -> project
	  */
	 public static function confs(): array {
		 return [];
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
	  */
	 public static function langs(): array {
		 return [];
	 }

	 /**
	  * @return string
	  * @throws \ReflectionException
	  */
	 public static function root():string{
	 	$reflected = new \ReflectionClass(static::class);
	 	return dirname($reflected->getFileName());
	 }
 }