<?php
namespace wfw\engine\lib\HTML\helpers\forms\validation;

/**
 * Applique une politique de validation des données ou du contexte d'un formulaire
 */
interface IValidationPolicy {
	/**
	 * Si la politique est verifiée, renvoie true, sinon il est préférable de lever une
	 * exception.
	 * @param array $data Données à valider
	 * @return bool
	 */
	public function apply(array &$data):bool;
}