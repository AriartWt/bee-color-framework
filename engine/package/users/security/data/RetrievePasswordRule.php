<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 14:54
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle validant un formulaire de reset de mot de passe.
 */
final class RetrievePasswordRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RetrievePasswordRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"Une de ces informations est incorrecte !",
			new RequiredFields("Ce champ est requis","login"),
			new IsLogin("Ceci n'est pas un login valide","login")
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