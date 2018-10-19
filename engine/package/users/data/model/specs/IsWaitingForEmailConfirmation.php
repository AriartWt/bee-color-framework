<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 15:21
 */

namespace wfw\engine\package\users\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;

/**
 * Match tous le sutilisateurs en attende de confirmation de leur adresse mail.
 */
final class IsWaitingForEmailConfirmation extends LeafSpecification{
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool{
		/** @var User $candidate */
		return $candidate->getState() instanceof UserWaitingForEmailConfirmation;
	}
}