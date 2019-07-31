<?php

namespace wfw\engine\package\general\security;

use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * General package access control policies
 */
class GeneralAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^general(/.*|)$"];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [ ["general" => [ "heartBeat" ]] ]
		];
	}

	/**
	 * @param bool $restrictMode If true, willd isable access to zipCodes.
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy(bool $restrictMode = true): array {
		if($restrictMode) return [
			NotFoundHook::class => [ ["^general/zipCodes(/.*|)$"] ]
		];
		else return [];
	}
}