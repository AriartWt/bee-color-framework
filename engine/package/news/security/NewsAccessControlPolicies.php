<?php

namespace wfw\engine\package\news\security;

use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\SecurityPolicy;

/**
 * Default news packages access policies
 */
class NewsAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^news(/.*|)$"];

	/**
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(): array {
		return [
			RequireAuthentification::class => [ ["news"], true ]
		];
	}
}