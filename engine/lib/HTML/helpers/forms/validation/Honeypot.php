<?php
namespace wfw\engine\lib\HTML\helpers\forms\validation;

use wfw\engine\lib\HTML\helpers\forms\errors\HoneypotFilled;

/**
 * Pot de miel pour les robots
 */
final class Honeypot implements IValidationPolicy{
	/** @var string[] $_fields */
	private $_fields;

	/**
	 * Honeypot constructor.
	 *
	 * @param string ...$fields Champs servant de pots de miel
	 */
	public function __construct(string... $fields) {
		$this->_fields = $fields;
	}

	/**
	 * Si la politique est verifiée, renvoie true, sinon il est préférable de lever une
	 * exception.
	 *
	 * @param array $data Données à valider
	 * @return bool
	 */
	public function apply(array &$data): bool {
		foreach(array_intersect(array_keys($data),$this->_fields) as $k){
			if(!empty($data[$k])) throw new HoneypotFilled(
				"Invisible field $k filled by user. This may be malicious submission."
			);
		}
		return true;
	}
}