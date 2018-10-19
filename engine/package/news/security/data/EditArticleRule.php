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
	 */
	public function __construct(ArticleIdRule $rule) {
		$this->_rule = new AndRule(
			"L'un de ces champs est incorrect !",
			$rule,
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","title"),
				new IsString("Ce champ doit être une chaine","title")
			),
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","content"),
				new IsString("Ce champ doit être une chaine","content")
			),
			new OrRule(
				"Ce champ est incorrect",
				new IsEmpty("Ce champ doit être vide","visual"),
				new IsString("Ce champ doit être une chaine","visual")
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