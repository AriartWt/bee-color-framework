<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/05/18
 * Time: 12:13
 */

namespace wfw\engine\package\news\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\news\data\model\objects\Article;

/**
 * Class IsArchived
 *
 * @package wfw\engine\package\news\data\model\specs
 */
final class IsArchived extends LeafSpecification
{

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool
	{
		/** @var Article $candidate */
		return $candidate->isArchived();
	}
}