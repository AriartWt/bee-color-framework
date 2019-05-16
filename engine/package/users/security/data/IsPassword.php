<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\package\users\domain\Password;

/**
 * Vérifie la validité d'un mot de passe.
 */
final class IsPassword extends ForEachFieldRule {
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		try{
			new Password($data ?? '');
			return preg_match(
				"#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])([a-zA-Z0-9]|[^a-zA-Z0-9]){4,128}$#",
				$data
			);
		}catch(\InvalidArgumentException $e){
			return false;
		}
	}
}