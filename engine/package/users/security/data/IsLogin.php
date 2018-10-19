<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/06/18
 * Time: 15:47
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\package\users\domain\Login;

/**
 * Régle de validation d'un login
 */
final class IsLogin extends ForEachFieldRule{
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		try{
			new Login($data);
			return true;
		}catch (\InvalidArgumentException $e){
			$this->changeMessage($e->getMessage());
			return false;
		}
	}
}