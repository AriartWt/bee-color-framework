<?php

namespace wfw\engine\core\conf;

/**
 * Base descriptor for sites
 */
class DefaultSiteDescriptor extends ModuleDescriptor {
	/**
	 * @return string
	 */
	public static function root(): string {
		return dirname(__DIR__,3)."/site";
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function cleanablePaths(): array {
		return array_merge(
			self::subdirectories("",["webroot"]),
			self::subdirectories("webroot",["uploads"])
		);
	}
}