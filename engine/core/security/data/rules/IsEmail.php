<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Vérifie si un champ est un email valide
 */
final class IsEmail extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return !is_null(filter_var($data,FILTER_VALIDATE_EMAIL,FILTER_NULL_ON_FAILURE));
	}
}