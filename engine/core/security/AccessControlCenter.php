<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/02/18
 * Time: 13:50
 */

namespace wfw\engine\core\security;

use wfw\engine\core\action\IAction;

/**
 * Centre de contrôle d'accés
 */
final class AccessControlCenter implements IAccessControlCenter {
	/**
	 * @var IAccessRule[] $_rules
	 */
	private $_rules;

	/**
	 * AccessControlCenter constructor.
	 *
	 * @param IAccessRuleFactory $factory        Factory
	 * @param array[]            ...$ruleClasses Liste des classes des règles à charger sous la
	 *                                           forme : $class => $params
	 * @throws \InvalidArgumentException
	 */
	public function __construct(IAccessRuleFactory $factory,array $ruleClasses) {
		$this->_rules = [];
		foreach ($ruleClasses as $ruleClass=>$params){
			if(is_a($ruleClass,IAccessRule::class,true)){
				$this->_rules[] = $factory->create($ruleClass,$params);
			}else{
				throw new \InvalidArgumentException(
					"$ruleClass doesn't implements ".IAccessRule::class);
			}
		}
	}

	/**
	 * Ajoute une régle de verification de permissions.
	 *
	 * @param IAccessRule $rule Règle à ajouter
	 */
	public function addRule(IAccessRule $rule): void {
		$this->_rules[] = $rule;
	}

	/**
	 * Vérifie les permissions d'accés à l'action $action en appliquant une a à une toutes les
	 * règles ajoutées avec addRule(), dans leur ordre d'ajout, jusqu'à ce que toutes les règles
	 * soient appliquées, ou que l'une d'entre elle ait retourné null. Retourner null revient à
	 * interrompre la chaine de verifications.
	 *
	 * @param IAction $action Action à tester
	 * @return IAccessPermission
	 */
	public function checkPermissions(IAction $action): IAccessPermission {
		foreach($this->_rules as $rule){
			$res = $rule->check($action);
			if(is_null($res)) return new AccessPermission(true);
			else if(!$res->isGranted()) return $res;
		}
		return new AccessPermission(true);
	}
}