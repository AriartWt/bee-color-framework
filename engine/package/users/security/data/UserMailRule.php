<?php
namespace wfw\engine\package\users\security\data;


use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Class UsermailRule
 * @package wfw\engine\package\users\security\data
 */
final class UserMailRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * UserMailRule constructor.
	 */
	public function __construct(){
		$this->_rule = new AndRule(
			"L'un des champs n'est pas valide",
			new RequiredFields("Ce champ est requis","email","id"),
			new IsEmail("Ceci n'est pas un email valide !","email"),
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