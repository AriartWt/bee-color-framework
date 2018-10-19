<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/06/18
 * Time: 17:20
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\rules\MatchRegexp;

/**
 * Vérifie un type d'utilisateur
 */
final class IsUserType extends MatchRegexp{
	/**
	 * UserTypeRule constructor.
	 * @param string $message
	 * @param string ...$fields
	 */
	public function __construct(string $message,string... $fields) {
		parent::__construct("/^(client|basic|admin)$/", $message,...$fields);
	}
}