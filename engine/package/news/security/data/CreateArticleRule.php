<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/04/18
 * Time: 09:47
 */

namespace wfw\engine\package\news\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsBool;
use wfw\engine\core\security\data\rules\IsEmpty;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\NotEmpty;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation pour les champ d'une création d'article
 */
final class CreateArticleRule implements IRule
{
	/**
	 * @var AndRule $_rule
	 */
	private $_rule;

	/**
	 * CreateArticleRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"Tous les champs sont requis",
			new RequiredFields("Ces champs sont requis","title","content","visual"),
			new IsString("Ce champ doit être une chaine valide","title","content","visual"),
			new NotEmpty("Ce champ ne eput pas être vide","title","content","visual"),
			new OrRule(
				"Ce champ doit être vide ou booléen",
				new IsEmpty("online"),
				new IsBool("online")
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