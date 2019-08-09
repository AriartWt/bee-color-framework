<?php

namespace wfw\engine\core\security;

use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\rules\ValidToken;
use wfw\engine\package\general\security\GeneralAccessControlPolicies;
use wfw\engine\package\uploader\security\UploaderAccessControlPolicies;
use wfw\engine\package\users\security\UsersAccessControlPolicies;

/**
 * WFW's own default security policy. Can be used to define the basic policy and will be used
 * if no security have been defined for a project.
 */
final class WFWDefaultSecurityPolicy extends SecurityPolicy {
	/**
	 * @param array $policies    [AccessRuleClass=>params]
	 * @param bool  $includeBase If true, will include default's WFW policies.
	 * @param array $templateArray Template array to specify the access policies order, as the
	 *                             method will use array_merge_recursive.
	 * @return array [AccessRuleClass => params]
	 */
	public static function accessPolicy(
		array $policies=[],
		bool $includeBase=true,
		array $templateArray = [
			RequireAuthentification::class => [],
			ValidToken::class => []
		]
	): array {
		$base = [];
		if($includeBase) $base = array_merge(
			UploaderAccessControlPolicies::accessPolicy(),
			UsersAccessControlPolicies::accessPolicy(),
			GeneralAccessControlPolicies::accessPolicy(),
			[ ValidToken::class => [] ]
		);
		return array_merge($templateArray,$base,$policies);
	}

	/**
	 * @param array $policies
	 * @param bool  $includeBase
	 * @param array $template
	 * @return array [HookClass => params ]
	 */
	public static function hooksPolicy(
		array $policies = [],
		bool $includeBase = true,
		array $template = [
			NotFoundHook::class => []
		]
	): array {
		$base = [];
		if($includeBase) $base = array_merge(
			UsersAccessControlPolicies::hooksPolicy(),
			GeneralAccessControlPolicies::hooksPolicy()
		);
		return array_merge($template,$base,$policies);
	}
}