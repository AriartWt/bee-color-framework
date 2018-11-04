<?php
namespace wfw\engine\package\contact\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\contact\data\model\objects\Contact;

/**
 * La prise de contact est archivée
 */
final class IsArchived extends LeafSpecification{
	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		/** @var Contact $candidate */
		return $candidate->isArchived();
	}
}