<?php
namespace wfw\engine\core\data\specification;

/**
 *  Spécification à implémenter
 */
abstract class LeafSpecification extends AbstractCompositeSpecification {
	public function __construct() {
		parent::__construct();
	}
}