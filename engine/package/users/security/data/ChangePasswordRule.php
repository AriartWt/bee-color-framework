<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 16:07
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\AreEquals;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Verifie un formulaire de changement de mot de passe.
 */
final class ChangePasswordRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ChangePasswordRule constructor.
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"L'une de ces informations est erronée",
			new RequiredFields("Ce champ est requis","password","password_confirm","old"),
			new AreEquals("Les mots de passes doivent être identiques","password","password_confirm"),
			new IsPassword("Ce n'est pas un mot de passe valide","password","password_confirm","old")
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