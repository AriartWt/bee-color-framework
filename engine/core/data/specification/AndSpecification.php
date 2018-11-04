<?php
namespace wfw\engine\core\data\specification;

/**
 *  effectue un ET logique avec les spécifications associées
 */
class AndSpecification extends AbstractCompositeSpecification {
	/**
	 *  AndSpecification constructor.
	 *
	 * @param ISpecification[] ...$specs Liste de specifications
	 */
	public function __construct(ISpecification ...$specs) {
		parent::__construct($specs);
	}

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		$res=true;
		foreach($this->_specs as $spec){
			if(!$spec->isSatisfiedBy($candidate)){
				$res=false;
				break;
			}
		}
		return $res;
	}
}