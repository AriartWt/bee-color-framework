<?php

namespace wfw\engine\core\conf;

/**
 * Interface for module configurators.
 */
interface IModuleDescriptor {
	/**
	 * @return array Dependency injection rules to add to the general DI
	 */
	public static function di():array;

	/**
	 * @return string Module root directory.
	 */
	public static function root():string;

	/**
	 * @return string[] List of file confs needed by the module.
	 *                  Confs applyance order is engine -> modules -> project
	 */
	public static function confs():array;

	/**
	 * @return string[] List of translation files indexed by lang
	 *                  ["en"=>[files], "fr"=>[files]]
	 */
	public static function langs():array;

	/**
	 * @return string[] List of model class that must be registered in site/config/site.models.php
	 */
	public static function models():array;

	/**
	 * @return array List of classes that implements IDomainEventListener => [constructParams]
	 */
	public static function domainEventListeners():array;

	/**
	 * @return array List of classes that implements ICommandHandlers => [constructParams]
	 */
	public static function commandHandlers():array;

	/**
	 * @return string[] List of class that implements ISecurityPolicy and that must be applyed.
	 */
	public static function securityPolicies():array;

	/**
	 * @return string[] List of paths that are cleanable while importing project.
	 */
	public static function cleanablePaths():array;
}