<?php
namespace wfw\engine\package\users\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;

/**
 * Match tous les utilisateurs activés
 */
final class IsEnabled extends LeafSpecification{
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool{
		/** @var User $candidate */
		return $candidate->getState() instanceof EnabledUser
			|| $candidate->getState() instanceof UserWaitingForEmailConfirmation
			|| $candidate->getState() instanceof UserWaitingForPasswordReset;
	}
}