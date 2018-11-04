<?php
namespace wfw\engine\package\contact\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\contact\data\model\objects\Contact;
use wfw\engine\package\contact\domain\ContactLabel;

/**
 * Class LabelIs
 *
 * @package wfw\engine\package\contact\data\model\specs
 */
final class LabelIs extends LeafSpecification{
	/** @var array $_labels */
	private $_labels;

	/**
	 * LabelIs constructor.
	 *
	 * @param ContactLabel ...$labels Liste de labels
	 */
	public function __construct(ContactLabel... $labels) {
		parent::__construct();
		$this->_labels = [];
		foreach($labels as $l){
			$this->_labels[(string)$l]=true;
		}
	}

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		/** @var Contact $candidate */
		return isset($this->_labels[(string) $candidate->getLabel()]);
	}
}