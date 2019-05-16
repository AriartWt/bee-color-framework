<?php
namespace wfw\engine\package\news\security\data;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsBool;
use wfw\engine\core\security\data\rules\IsEmpty;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\NotEmpty;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation pour les champ d'une création d'article
 */
final class CreateArticleRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * CreateArticleRule constructor.
	 *
	 * @param ITranslator $translator
	 * @param int         $maxTitleLength   Taille maximale du titre
	 * @param int         $maxVisualLength  Taille maximale de l'url vers le visuel
	 * @param int         $maxContentLength Taille maximale du contenu (balises comprises)
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ITranslator $translator,
		int $maxTitleLength = 512,
		int $maxVisualLength = 2048,
		int $maxContentLength = 2000000
	){
		$key = "server/engine/package/news/forms";
		$this->_rule = new AndRule(
			$translator->get("$key/GENERAL_ERROR"),
			new RequiredFields($translator->get("$key/REQUIRED"),"title","content","visual"),
			new IsString($translator->get("$key/INVALID_STRING"),"title","content","visual"),
			new NotEmpty($translator->get("$key/NOT_EMPTY"),"title","content","visual"),
			new MaxStringLength(
				$translator->getAndReplace("$key/MAX_TITLE_LENGTH",$maxTitleLength),
				$maxTitleLength,
				"title"
			),
			new MaxStringLength(
				$translator->getAndReplace("$key/MAX_VISUAL_LENGTH",$maxVisualLength),
				$maxVisualLength,
				"visual"
			),
			new MaxStringLength(
				$translator->getAndReplace("$key/MAX_CONTENT_LENGTH",$maxContentLength),
				$maxContentLength,
				"content"
			),
			new OrRule(
				$translator->get("$key/EMPTY_OR_BOOLEAN"),
				new IsEmpty("online"),
				new IsBool("online")
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