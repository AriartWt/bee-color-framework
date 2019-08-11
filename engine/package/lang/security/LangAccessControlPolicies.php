<?php

namespace wfw\engine\package\lang\security;

use wfw\engine\core\security\SecurityPolicy;

/**
 * Lang package access control policies
 */
final class LangAccessControlPolicies extends SecurityPolicy {
	public const DISABLE = ["^lang(/.*|)"];
}