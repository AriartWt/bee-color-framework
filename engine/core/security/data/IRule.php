<?php
namespace wfw\engine\core\security\data;

/**
 * Règle de validation de données.
 */
interface IRule {
	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data):IRuleReport;
}