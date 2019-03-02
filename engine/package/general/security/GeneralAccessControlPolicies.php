<?php

namespace wfw\engine\package\general\security;

/**
 * General package access control policies
 */
final class GeneralAccessControlPolicies {
	public const DISABLE = ["^general(/.*|)"];
	public const DISABLE_ZIP_CODES = ["^general/zipCodes(/.*|)"];
	public const DISABLE_HEART_BEAT = ["^general/heartBeat.*"];
}