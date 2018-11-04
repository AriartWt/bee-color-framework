<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Véirife si une donnée est vide.
 */
final class IsEmpty extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return empty($data);
	}
}