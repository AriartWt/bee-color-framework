<?php

namespace wfw\engine\package\uploader\security;

use wfw\engine\core\command\security\rules\UserTypeBasedCommandAccessRule;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * Default uploader package access policies
 */
class UploaderAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^uploader(/.*|)$"];

	/**
	 * @return array
	 */
	public static function commandsPolicy(): array {
		return [
			UserTypeBasedCommandAccessRule::class => []
		];
	}

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [ ["uploader"] ]
		];
	}
}