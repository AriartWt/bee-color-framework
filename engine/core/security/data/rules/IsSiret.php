<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/07/18
 * Time: 15:33
 */

namespace wfw\engine\core\security\data\rules;

/**
 * Vérifie la validité d'un numéro de SIREN
 */
final class IsSiret extends IsSiren {
	/**
	 * @param mixed $data Données
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(is_string($data)){
			$data=preg_replace("/[^0-9]+/",'', $data);
			if(preg_match("/^[0-9]{14}$/",$data)){
				$siren=parent::applyOn(substr($data,0,9));
				if($siren) return $this->lunh($data);
				else false;
			}else false;
		}else false;
	}
}