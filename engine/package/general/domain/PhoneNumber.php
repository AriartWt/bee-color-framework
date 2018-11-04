<?php
namespace wfw\engine\package\general\domain;

/**
 * Numéro de téléphone
 */
class PhoneNumber {
	/** @var string $_number */
	private $_number;

	/**
	 * PhoneNumber constructor.
	 * @param string $number
	 * @param string $defCountryCode Code pays ajouté si aucun code n'est trouvé dans le
	 *                               numéro de téléphone
	 * @param array $patterns Liste des pattern de remplacement pour le $defContryCode
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $number,string $defCountryCode = "33",array $patterns = ['/^0/']) {
		$matches = [];
		if(!preg_match("/^(\+|00|\d)".
			"((9[679]|8[035789]|6[789]|5[90]|42|3[578]|2[1-689])|9[0-58]|8[1246]|6[0-6]|5[1-8]|4[013-9]|3[0-469]|2[70]|7|1|)"
			."(?:\W*\d){0,13}\d$/",$number,$matches)
		) throw new \InvalidArgumentException("'$number' is not a valid phone number !");
		//on remplace le premier caractère par le code pays par défaut
		if($matches[1] !== '+' && $matches[1] !== '00'){
			foreach($patterns as $p){
				$number = preg_replace($p,"+$defCountryCode",$number);
			}
		}else if($matches[1] === '00') $number = preg_replace("/^00/","+",$number);
		//on supprime les séparateurs
		$this->_number = preg_replace('/[^0-9+]/','',$number);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_number;
	}
}