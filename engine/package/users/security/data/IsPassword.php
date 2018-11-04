<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/06/18
 * Time: 15:59
 */

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
			$res =  preg_match(
				"#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])([a-zA-Z0-9]|[^a-zA-Z0-9]){4,128}$#",
				$data
			);
			if(!$res) $this->changeMessage(
				"Votre mot de passe doit être composé de chiffre, lettres majuscules et minuscules "
				."et au moins un caractère spécial pour une taille maximale de 128 caractères."
			);
			return $res;
		}catch(\InvalidArgumentException $e){
			return false;
		}
	}
}