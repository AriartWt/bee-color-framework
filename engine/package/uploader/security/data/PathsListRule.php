<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/05/18
 * Time: 12:03
 */

namespace wfw\engine\package\uploader\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\rules\IsArrayOf;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MatchRegexp;
use wfw\engine\core\security\data\rules\MaxArrayLength;
use wfw\engine\core\security\data\rules\RequiredFields;

/**
 * Class UploadPathRule
 *
 * @package wfw\engine\package\uploader\security\data
 */
final class PathsListRule implements IRule {
	/** @var AndRule $_rule */
	private $_rule;

	/**
	 * UploadPathRule constructor.
	 *
	 * @param int $maxPathLength Taille maximale d'un chemin
	 * @param int $maxPaths      Nombre maximum de chemins traités en une seule requête
	 */
	public function __construct(int $maxPathLength = 2048,int $maxPaths = 10000) {
		$this->_rule = new AndRule(
			"Invalid data",
			new RequiredFields("Ce champ est requis","paths"),
			new IsArrayOf("Ce n'est pas une chaine de caractères valide !",function($d)use($maxPathLength){
				return is_string($d) && strlen($d) < $maxPathLength;
			},"paths"),
			new MaxArrayLength(
				"Ce tableau ne peut pas contenir plus de $maxPaths éléments",
				$maxPaths,
				"paths"
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport { return $this->_rule->applyTo($data); }
}