<?php

namespace wfw\engine\package\miel\security;

use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * Default miel package access policies
 */
class MielAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^uploader(/.*|)$"];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [ ["miel" ], true ]
		];
	}
}