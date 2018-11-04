<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 07:55
 */

namespace wfw\engine\package\miel\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\NotEmpty;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation de base pour la fonctionnalité miel.
 */
final class MielRule implements IRule {
	/** @var AndRule $_mainRule */
	private $_mainRule;

	/**
	 * MielRule constructor.
	 *
	 * @param string $maxDataLength
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $maxDataLength="66000") {
		$this->_mainRule = new AndRule(
			"Tous les champos sont requis !",
			new RequiredFields(
				"Ce champ doit être précisé","miel_key","miel_data"
			),
			new MaxStringLength(
				"Ce champ ne peut pas excéder les 512 caractères de long !",
				512,
				"miel_key"
			),
			new MaxStringLength(
				"Ce champ ne peut pas excéder les $maxDataLength caractères de long!",
				$maxDataLength,
				"miel_data"
			),
			new NotEmpty("Ce champ ne peut pas être vide","miel_key")
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		return $this->_mainRule->applyTo($data);
	}
}