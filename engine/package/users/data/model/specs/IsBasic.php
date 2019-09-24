<?php
namespace wfw\engine\package\users\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\types\Basic;

/**
 * Vérifie si un utilisateur est de type Basic
 */
final class IsBasic extends LeafSpecification{
	
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		/** @var User $candidate */
		return $candidate->getType() instanceof Basic;
	}
}