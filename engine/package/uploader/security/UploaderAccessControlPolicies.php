<?php

namespace wfw\engine\package\uploader\security;

/**
 * Default uploader package access policies
 */
final class UploaderAccessControlPolicies{
	public const REQUIRE_AUTH = ["uploader"];
	public const DISABLE = ["^uploader(/.*|)"];
}