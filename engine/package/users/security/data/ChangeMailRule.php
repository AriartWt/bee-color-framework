<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 14:46
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Permet de valider un formulaire de demande de changement de mail.
 */
final class ChangeMailRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * ChangeMailRule constructor.
	 */
	public function __construct() {
		$this->_rule = new AndRule(
			"L'une de ces information est incorrecte !",
			new RequiredFields("Ce champ est requis","email"),
			new IsEmail("Ceci n'est pas un email valide", "email")
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