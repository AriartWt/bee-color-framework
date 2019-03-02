<?php

namespace wfw\engine\package\users\security;

/**
 * Users package control access policies
 */
final class UsersAccessControlPolicies{
	public const REQUIRE_AUTH = [
		"users"=>[
			"admin",
			"changeMail",
			"changePassword",
			"logout"
		]
	];
	/**
	 * Use this into a NotFoundHook if you don't want to enable the all user package (some
	 * actions are useless if app/website no need for user auto registration)
	 */
	public const RESTRICT_MODE = [
		"^users/(change|confirm|cancel|forgotten|register|resend|reset).*"
	];
	/**
	 * Use this to disable the all user package
	 */
	public const DISABLE = [
		"^users(/.*|)"
	];
}