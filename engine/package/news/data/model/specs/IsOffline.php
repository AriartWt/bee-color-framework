<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/05/18
 * Time: 12:11
 */

namespace wfw\engine\package\news\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\news\data\model\objects\Article;

/**
 * L'article est hors ligne
 */
final class IsOffline extends LeafSpecification
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
		return !$candidate->isOnline();
	}
}