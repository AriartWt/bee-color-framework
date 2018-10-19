<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/06/18
 * Time: 16:49
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\AreEquals;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Verifie les données nécessaires à la création d'un nouveau mot de passe.
 */
final class PasswordResetRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * PasswordResetRule constructor.
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"Certaines informations sont erronées",
			new RequiredFields("Ce champ est requis","password","password_confirm","id"),
			new IsPassword("Ceci n'est pas un mot de passe valide","password"),
			new AreEquals("Les mots de passe doivent être égaux !","password","password_confirm"),
			new IsUUID("Ceci n'est pas un identifiant valide !","id")
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