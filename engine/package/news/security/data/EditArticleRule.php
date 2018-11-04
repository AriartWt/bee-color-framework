<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/04/18
 * Time: 10:13
 */

namespace wfw\engine\package\news\security\data;

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
final class EditArticleRule implements IRule
{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * EditArticleRule constructor.
	 *
	 * @param ArticleIdRule $rule
	 * @param int           $maxTitleLength
	 * @param int           $maxVisualLength
	 * @param int           $maxContentLength
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ArticleIdRule $rule,
		int $maxTitleLength = 512,
		int $maxVisualLength = 2048,
		int $maxContentLength = 2000000
	) {
		$this->_rule = new AndRule(
			"L'un de ces champs est incorrect !",
			$rule,
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","title"),
				new AndRule(
					"Ce titre n'est pas valide !",
					new MaxStringLength(
						"Le titre d'un article ne peut pas dépasser les $maxTitleLength caractères",
						$maxTitleLength,
						"title"
					),
					new IsString("Ce champ doit être une chaine","title")
				)
			),
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","content"),
				new AndRule(
					"Ce contenu n'est pas valide !",
					new MaxStringLength(
						"Le contenu d'un article ne peut pas dépasser les $maxContentLength caractères",
						$maxTitleLength,
						"content"
					),
					new IsString("Ce champ doit être une chaine","content")
				)
			),
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","visual"),
				new AndRule(
					"Ce visuel n'est pas valide !",
					new MaxStringLength(
						"Le chemin vers le visuel d'un article ne peut pas dépasser les $maxVisualLength caractères",
						$maxVisualLength,
						"visual"
					),
					new IsString("Ce champ doit être une chaine","visual")
				)
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport
	{
		return $this->_rule->applyTo($data);
	}
}