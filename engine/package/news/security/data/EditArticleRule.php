<?php
namespace wfw\engine\package\news\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsEmpty;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MaxStringLength;

/**
 * Régle pour les champs d'édition
 */
final class EditArticleRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * EditArticleRule constructor.
	 *
	 * @param ArticleIdRule $rule
	 * @param ITranslator   $translator
	 * @param int           $maxTitleLength
	 * @param int           $maxVisualLength
	 * @param int           $maxContentLength
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ArticleIdRule $rule,
		ITranslator $translator,
		int $maxTitleLength = 512,
		int $maxVisualLength = 2048,
		int $maxContentLength = 2000000
	) {
		$key = "server/engine/package/news/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			$rule,
			new OrRule(
				$translator->get("$key/INVALID_FIELD"),
				new IsEmpty($translator->get("$key/MUST_BE_EMPTY"),"title"),
				new AndRule(
					$translator->get("$key/INVALID_TITLE"),
					new MaxStringLength(
						$translator->getAndReplace("$key/MAX_TITLE_LENGTH",$maxTitleLength),
						$maxTitleLength,
						"title"
					),
					new IsString($translator->get("$key/INVALID_STRING"),"title")
				)
			),
			new OrRule(
				$translator->get("$key/INVALID_FIELD"),
				new IsEmpty($translator->get("$key/MUST_BE_EMPTY"),"content"),
				new AndRule(
					$translator->get("$key/INVALID_CONTENT"),
					new MaxStringLength(
						$translator->getAndReplace("$key/MAX_CONTENT_LENGTH",$maxContentLength),
						$maxContentLength,
						"content"
					),
					new IsString($translator->get("$key/INVALID_STRING"),"content")
				)
			),
			new OrRule(
				$translator->get("$key/INVALID_FIELD"),
				new IsEmpty($translator->get("$key/MUST_BE_EMPTY"),"visual"),
				new AndRule(
					$translator->get("$key/INVALID_VISUAL"),
					new MaxStringLength(
						$translator->getAndReplace("$key/MAX_VISUAL_LENGTH",$maxVisualLength),
						$maxVisualLength,
						"visual"
					),
					new IsString($translator->get("$key/INVALID_STRING"),"visual")
				)
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