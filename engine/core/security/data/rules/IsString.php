<?php
namespace wfw\engine\core\security\data\rules;


use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Vérifie qu'une donnée est une chaine de caractères.
 */
final class IsString extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return is_string($data);
	}
}