<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Teste si chacun des champs est un entier
 */
final class IsInt extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return !is_null(filter_var($data,FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE));
	}
}