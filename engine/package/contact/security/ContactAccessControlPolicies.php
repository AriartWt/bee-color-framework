<?php

namespace wfw\engine\package\contact\security;

use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * Default contact package access policies
 */
class ContactAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^contact(/.*|)$"];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [
				[ "contact" ], true
			]
		];
	}
}