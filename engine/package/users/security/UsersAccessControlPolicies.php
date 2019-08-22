<?php

namespace wfw\engine\package\users\security;

use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\command\security\rules\UserTypeBasedCommandAccessRule;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;
use wfw\engine\package\users\command\CancelPasswordRetrieving;
use wfw\engine\package\users\command\ChangePassword;
use wfw\engine\package\users\command\ChangeUserMail;
use wfw\engine\package\users\command\ConfirmUserMailChange;
use wfw\engine\package\users\command\ConfirmUserRegistration;
use wfw\engine\package\users\command\RegisterUser;
use wfw\engine\package\users\command\ResetPassword;
use wfw\engine\package\users\command\RetrievePassword;

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
				]
			]
		];
	}

	/**
	 * @return array
	 */
	public static function commandsPolicy(): array {
		return [
			/*UserTypeBasedCommandAccessRule::class => [
				UserTypeBasedCommandAccessRule::PUBLIC => [
					RegisterUser::class,
					ResetPassword::class,
					RetrievePassword::class,
					ConfirmUserRegistration::class
				],
				UserTypeBasedCommandAccessRule::ANY => [
					ChangeUserMail::class,
					ConfirmUserMailChange::class,
					ChangePassword::class
				]
			]*/
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