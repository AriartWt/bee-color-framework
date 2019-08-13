<?php

namespace wfw\engine\core\security;

use wfw\engine\core\action\NotFoundHook;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\security\rules\AllCommandsAllowed;
use wfw\engine\core\command\security\rules\UserTypeBasedCommandAccessRule;
use wfw\engine\core\security\rules\RequireAuthentification;
use wfw\engine\core\security\rules\ValidToken;
use wfw\engine\package\general\security\GeneralAccessControlPolicies;
use wfw\engine\package\lang\security\LangAccessControlPolicies;
use wfw\engine\package\uploader\security\UploaderAccessControlPolicies;
use wfw\engine\package\users\security\UsersAccessControlPolicies;

/**
 * WFW's own default security policy. Can be used to define the basic policy and will be used
 * if no security have been defined for a project.
 */
final class WFWDefaultSecurityPolicy extends SecurityPolicy {
	/**
	 * @param array $policies
	 * @param bool  $includeBase   If true, will include default's WFW policies
	 * @param array $templateArray Template array to specify the query policy order
	 * @return array
	 */
	public static function queriesPolicy(
		array $policies = [],
		bool $includeBase=true,
		array $templateArray=[
			UserTypeBasedCommandAccessRule::class => []
		]
	): array {
		$base = [];
		if($includeBase) $base = [
			UploaderAccessControlPolicies::queriesPolicy(),
			UsersAccessControlPolicies::queriesPolicy(),
			GeneralAccessControlPolicies::queriesPolicy(),
			LangAccessControlPolicies::queriesPolicy()
		];
		return array_merge($templateArray,$base,$policies);
	}

	/**
	 * @param array $policies      [(string|UserType::class)target => [ICommand:class]]
	 * @param bool  $includeBase   If true, will include default's WFW policies
	 * @param array $templateArray Template array to specify the command policies order
	 * @return array
	 */
	public static function commandsPolicy(
		array $policies = [],
		bool $includeBase=true,
		array $templateArray=[
			UserTypeBasedCommandAccessRule::class => []
		]
	): array {
		$base = [];
		if($includeBase) $base = [
			UploaderAccessControlPolicies::commandsPolicy(),
			UsersAccessControlPolicies::commandsPolicy(),
			GeneralAccessControlPolicies::commandsPolicy(),
			LangAccessControlPolicies::commandsPolicy(),
			[UserTypeBasedCommandAccessRule::class => [[
				UserTypeBasedCommandAccessRule::ANY => [
					ICommand::class => true
				],
				UserTypeBasedCommandAccessRule::PUBLIC => [
					ICommand::class => true
				]
			]]]
		];
		return array_merge($templateArray,$base,$policies);
	}

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
		if($includeBase) $base = [
			UploaderAccessControlPolicies::accessPolicy(),
			UsersAccessControlPolicies::accessPolicy(),
			GeneralAccessControlPolicies::accessPolicy(),
			LangAccessControlPolicies::accessPolicy(),
			[ ValidToken::class => [] ]
		];
		/*var_dump(array_merge($templateArray,$base,$policies));*/
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
		if($includeBase) $base = [
			UploaderAccessControlPolicies::hooksPolicy(),
			UsersAccessControlPolicies::hooksPolicy(),
			GeneralAccessControlPolicies::hooksPolicy(),
			LangAccessControlPolicies::hooksPolicy()
		];
		return array_merge($template,$base,$policies);
	}
}