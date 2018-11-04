<?php
namespace wfw\engine\package\users\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\states\DisabledUser;

/**
 * Match tous les utilisateurs désactivé
 */
final class IsDisabled extends LeafSpecification{
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool{
		/** @var User $candidate */
		return $candidate->getState() instanceof DisabledUser;
	}
}