<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Verifie si un numéro valide la formule de lunh (modulo 10)
 * @see https://fr.wikipedia.org/wiki/Formule_de_Luhn
 */
class IsLunh extends ForEachFieldRule{
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return $this->lunh($data);
	}

	/**
	 * Applique l'algorythme de lunh aux données
	 * @param mixed $data Données à tester
	 * @return bool
	 */
	protected function lunh($data):bool{
		if(!is_string($data)) return false;
		$res=0;
		$data=str_split($data);
		foreach($data as $k=>$v){
			if(($k+1)%2==0){
				$tmp=(2*$v);
				if(strlen($tmp)>1){
					$tmp=str_split($tmp);
					$res+=(intval($tmp[0])+intval($tmp[1]));
				}else $res+=$tmp;
			}else $res+=$v;
		}
		return $res%10===0;
	}
}