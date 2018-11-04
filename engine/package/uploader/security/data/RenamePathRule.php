<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 10:55
 */

namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsArrayOf;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MaxArrayLength;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Régle de validation pour des données en vue d'un renommage de fichier/dossier.
 */
final class RenamePathRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * RenamePathRule constructor.
	 *
	 * @param int $maxPathLength Taille maximale d'un chemin
	 * @param int $maxPath Nombre de chemins traités simultanément
	 */
	public function __construct(int $maxPathLength = 2048, int $maxPath = 10000) {
		$this->_rule = new AndRule(
			"Les données sont invalides !",
			new RequiredFields("Ces champs sont requis : ","oldPaths","newPaths"),
			new IsArrayOf("Ce ne sont pas des chaînes valides !",function($d)use($maxPathLength){
				return is_string($d) && strlen($d)<$maxPathLength;
			},"oldPaths","newPaths"),
			new MaxArrayLength(
				"Ce tableau ne peut pas contenir plus de $maxPath élements",
				$maxPath,
				"oldPaths","newPaths"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport { return $this->_rule->applyTo($data); }
}