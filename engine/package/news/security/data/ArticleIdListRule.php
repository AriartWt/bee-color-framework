<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/05/18
 * Time: 12:21
 */

namespace wfw\engine\package\news\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Vérifie si chaque champs correspond à une liste d'identifiants.
 */
final class ArticleIdListRule extends ForEachFieldRule {
	public function __construct() {
		parent::__construct("L'un des identifiants n'est pas valide !", "ids");
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool{
		if(!is_array($data)) return false;
		$rule = new IsUUID("Cet identifiant est invalide !",...array_keys($data));
		return $rule->applyTo($data)->satisfied();
	}
}