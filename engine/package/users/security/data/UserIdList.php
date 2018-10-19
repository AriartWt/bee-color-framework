<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/06/18
 * Time: 16:41
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Liste d'identifiant d'utilisateurs
 */
final class UserIdList extends ForEachFieldRule{
	/**
	 * UserIdList constructor.
	 */
	public function __construct() {
		parent::__construct("L'un des identifiants n'est pas valide !", "ids");
	}
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(!is_array($data)) return false;
		$rule = new IsUUID("Cet identifiant est invalide !",...array_keys($data));
		return $rule->applyTo($data)->satisfied();
	}
}