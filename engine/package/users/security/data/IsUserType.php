<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\rules\MatchRegexp;

/**
 * Vérifie un type d'utilisateur
 */
final class IsUserType extends MatchRegexp{
	public const DEFAULT_TYPES=["client","basic","admin"];

	/**
	 * UserTypeRule constructor.
	 *
	 * @param string $message
	 * @param array  $roles
	 * @param string ...$fields
	 */
	public function __construct(string $message,array $roles=self::DEFAULT_TYPES,string... $fields) {
		parent::__construct("/^(".implode("|",$roles).")$/", $message,...$fields);
	}
}