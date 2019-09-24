<?php
namespace wfw\engine\package\users\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Liste d'identifiant d'utilisateurs
 */
final class UserIdList extends ForEachFieldRule{
	/** @var int $_length */
	private $_length;

	/**
	 * UserIdList constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxLength Nombre maximum d'utilisateurs traités en une seule fois
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ITranslator $translator,int $maxLength = 10000) {
		$key = "server/engine/package/users/forms";
		parent::__construct($translator->get("$key/INVALID_ID_LIST"), "ids");
		if($maxLength <= 0) throw new \InvalidArgumentException("maxLength must be > 0");
		$this->_length = $maxLength;
	}
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(!is_array($data)) return false;
		if(count($data)>$this->_length || count($data)===0) return false;
		$rule = new IsUUID("Cet identifiant est invalide !",...array_keys($data));
		return $rule->applyTo($data)->satisfied();
	}
}