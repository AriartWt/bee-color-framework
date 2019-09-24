<?php
namespace wfw\engine\lib\HTML\helpers\forms\validation;

/**
 * Permet d'appliquer plusieurs politiques de validation
 */
final class MultiValidationPolicy implements IValidationPolicy{
	/** @var IValidationPolicy[] $_policies */
	private $_policies;

	/**
	 * MultiValidationPolicy constructor.
	 * @param IValidationPolicy ...$policies Politique de validation à appliquer
	 */
	public function __construct(IValidationPolicy... $policies) {
		$this->_policies = $policies;
	}

	/**
	 * Ajoute des politiques de validation
	 * @param IValidationPolicy ...$policies Politiques de validation à ajouter
	 */
	public function addPolicies(IValidationPolicy... $policies){
		$this->_policies = array_merge($this->_policies,$policies);
	}

	/**
	 * @return IValidationPolicy[] Liste des politiques de validation
	 */
	public function getPolicies():array{
		return $this->_policies;
	}

	/**
	 * Si la politique est verifiée, renvoie true, sinon il est préférable de lever une
	 * exception.
	 * @param array $data Données à valider
	 * @return bool
	 */
	public function apply(array &$data): bool {
		foreach($this->_policies as $policy){
			if(!$policy->apply($data)) return false;
		}
		return true;
	}
}