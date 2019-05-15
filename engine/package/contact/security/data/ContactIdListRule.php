<?php
namespace wfw\engine\package\contact\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Vérifie si chaque champs correspond à une liste d'identifiants.
 */
final class ContactIdListRule extends ForEachFieldRule {
	/** @var int $_length */
	private $_length;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ContactIdListRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxLength Nombre maximum de contact pouvant être traités en une seule fois
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ITranslator $translator,int $maxLength = 10000) {
		parent::__construct(
			$translator->get("server/engine/package/contact/forms/INVALID_ID_IN_LIST"),
			"ids"
		);
		$this->_translator = $translator;
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
		$rule = new IsUUID(
			$this->_translator->get("server/engine/package/contact/forms/INVALID_ID"),
			...array_keys($data)
		);
		return $rule->applyTo($data)->satisfied();
	}
}