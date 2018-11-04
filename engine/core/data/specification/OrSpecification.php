<?php
namespace wfw\engine\core\data\specification;

/**
 *  Effectue un OR sur ses specs
 */
class OrSpecification extends AbstractCompositeSpecification {
	/**
	 * OrSpecification constructor.
	 *
	 * @param ISpecification[] ...$specs
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
		$res=false;
		foreach($this->_specs as $spec){
			if($spec->isSatisfiedBy($candidate)){
				$res = true;
				break;
			}
		}
		return $res;
	}
}