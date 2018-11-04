<?php
namespace wfw\engine\core\data\specification;

/**
 *  Applique une négation logique au résultat de la spécification associée
 */
class NotSpecification extends AbstractCompositeSpecification {
	/**
	 *  NotSpecification constructor.
	 *
	 * @param ISpecification $spec Specification à inverser
	 */
	public function __construct(ISpecification $spec) {
		parent::__construct([$spec]);
	}

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		return !$this->_specs[0]->isSatisfiedBy($candidate);
	}
}