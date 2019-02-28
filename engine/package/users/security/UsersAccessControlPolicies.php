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
}