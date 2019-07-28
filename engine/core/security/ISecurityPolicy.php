<?php

namespace wfw\engine\core\security;

/**
 * Default security interface
 */
interface ISecurityPolicy {
	/** @var array This constant must be defined to disable a full module */
	public const DISABLE = [];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy():array;

	/**
	 * @return array [CommandAccessRuleClass => params]
	 */
	public static function commandsPolicy():array;

	/**
	 * @return array [QueryAccessRuleClass => params]
	 */
	public static function queriesPolicy():array;

	/**
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy():array;
}