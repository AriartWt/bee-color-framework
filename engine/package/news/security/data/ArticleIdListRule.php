<?php
namespace wfw\engine\package\news\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\core\security\data\rules\IsUUID;

/**
 * Vérifie si chaque champs correspond à une liste d'identifiants.
 */
final class ArticleIdListRule extends ForEachFieldRule {
	/** @var int $_length */
	private $_length;
	private $_translator;

	/**
	 * ArticleIdListRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxLength Nombre maximum d'éléments dans le tableau
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ITranslator $translator,int $maxLength = 10000) {
		parent::__construct(
			$translator->get("server/engine/package/news/forms/INVALID_ID_IN_LIST"),
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
			$this->_translator->get("server/engine/package/news/forms/INVALID_ID"),
			...array_keys($data)
		);
		return $rule->applyTo($data)->satisfied();
	}
}