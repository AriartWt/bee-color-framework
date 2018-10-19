<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/06/18
 * Time: 15:36
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\RequiredFields;
use wfw\engine\package\users\data\model\IUserModelAccess;

/**
 * Régle de validation des données pour la création d'un utilisateur
 */
final class RegisterUserRule implements IRule{
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RegisterUserRule constructor.
	 * @param IUserModelAccess $access
	 */
	public function __construct(IUserModelAccess $access) {
		$this->_rule = new AndRule(
			"Tous les champs sont requis",
			new RequiredFields("Ces champs sont requis !","login","password","email","type"),
			new IsLogin("Ce login n'est pas valide !","login"),
			new IsUniqueLogin($access,"Ce login n'est pas disponible","login"),
			new IsPassword("Ce mot de passe n'est pas valide !","password"),
			new IsUserType("Les seuls types d'utilisateurs supportés sont admin, client et basic","type"),
			new IsEmail("Ceci n'est pas une adresse mail valide !","email")
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