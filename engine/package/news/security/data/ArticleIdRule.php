<?php
namespace wfw\engine\package\news\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle concernant les identifiant d'article
 */
final class ArticleIdRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ArticleIdRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule("L'identifiant de l'article est obligatoire",
			new RequiredFields("L'identifiant de l'article doit être précisé.","article_id"),
			new IsUUID("Cet identifiant n'est pas valide","article_id")
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