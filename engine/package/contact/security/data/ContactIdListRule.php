<?php
namespace wfw\engine\package\contact\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Vérifie si chaque champs correspond à une liste d'identifiants.
 */
final class ContactIdListRule extends ForEachFieldRule {
	/** @var int $_length */
	private $_length;

	/**
	 * ContactIdListRule constructor.
	 *
	 * @param int $maxLength Nombre maximum de contact pouvant être traités en une seule fois
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $maxLength = 10000) {
		parent::__construct("L'un des identifiants n'est pas valide !", "ids");
		if($maxLength <= 0) throw new \InvalidArgumentException("maxLength must be > 0");
		$this->_length = $maxLength;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool{
		if(!is_array($data)) return false;
		if(count($data)>$this->_length || count($data)===0) return false;
		$rule = new IsUUID("Cet identifiant est invalide !",...array_keys($data));
		return $rule->applyTo($data)->satisfied();
	}
}