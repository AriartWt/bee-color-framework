<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 02:29
 */

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