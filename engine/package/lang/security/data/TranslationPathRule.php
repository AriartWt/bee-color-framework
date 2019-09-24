<?php
namespace wfw\engine\package\lang\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Class TranslationPathRule
 *
 * @package wfw\engine\package\lang\security\data
 */
final class TranslationPathRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * TranslationPathRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxKeyLength
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ITranslator $translator, int $maxKeyLength=512) {
		$key = "server/engine/package/lang/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"lang_path"),
			new IsString($translator->get("$key/INVALID_STRING"),"lang_path"),
			new MaxStringLength(
				$translator->getAndReplace("$key/TOO_LARGE_KEY",$maxKeyLength),
				$maxKeyLength,
				"lang_path"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		return $this->_rule->applyTo($data);
	}
}