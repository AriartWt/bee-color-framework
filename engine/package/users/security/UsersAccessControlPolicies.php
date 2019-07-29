<?php

namespace wfw\engine\package\users\security;

use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * Users package control access policies
 */
class UsersAccessControlPolicies extends SecurityPolicy{
	/**
	 * Use this to disable the all user package
	 */
	public const DISABLE = [ "^users(/.*|)$" ];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [
				[
					"users"=>[
						"admin",
						"changeMail",
						"changePassword",
						"logout"
					]
				], true
			]
		];
	}

	/**
	 * @param bool $restrictMode
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy(bool $restrictMode = true): array {
		if($restrictMode) return [
			NotFoundHook::class => [
				["^users/(change|confirm|cancel|forgotten|register|resend|reset).*$"]
			]
		];
		else return [];
	}
}