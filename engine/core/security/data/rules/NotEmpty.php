<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Teste si la donnée n'est pas considérée comme vide
 */
final class NotEmpty extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return !empty($data);
	}
}