<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/06/18
 * Time: 15:49
 */

namespace wfw\engine\package\users\data\model\specs;


use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\types\Admin;

/**
 * Vérifie si un utilisateur est un administrateur
 */
final class IsAdmin extends LeafSpecification {
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		/** @var User $candidate */
		return $candidate->getType() instanceof Admin;
	}
}