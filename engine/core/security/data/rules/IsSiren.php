<?php
namespace wfw\engine\core\security\data\rules;

/**
 * Vérifie qu'un numéro SIREN est valide
 */
class IsSiren extends IsLunh {
	/**
	 * @param mixed $data
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(is_string($data)){
			$data=preg_replace("/[^0-9]+/",'', $data);
			if(preg_match("/^[0-9]{9}$/",$data)){
				return parent::applyOn($data);
			}else return false;
		}else return false;
	}
}