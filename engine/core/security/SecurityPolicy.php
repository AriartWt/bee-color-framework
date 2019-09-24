<?php

namespace wfw\engine\core\security;

/**
 * SecurityPolicy baseclass. Must be extended in order to ensure that a further update will not
 * break any SecurityPolicy if new methods are added to the ISecurityPolicy interface.
 */
class SecurityPolicy implements ISecurityPolicy {
	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [];
	}

	/**
	 * @return array [CommandAccessRuleClass => params]
	 */
	public static function commandsPolicy(): array {
		return [];
	}

	/**
	 * @return array [QueryAccessRuleClass => params]
	 */
	public static function queriesPolicy(): array {
		return [];
	}

	/**
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy(): array {
		return [];
	}
}