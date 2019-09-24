<?php
namespace wfw\engine\core\data\model;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\core\data\specification\LeafSpecification;

/**
 * Specification produite par une ExternalModelQuery
 */
abstract class CrossModelSpecification extends LeafSpecification {
	/** @var IDTO[] $_data */
	private $_data;

	/**
	 * IExternalModelSpecification constructor.
	 *
	 * @param IDTO[] $data
	 */
	public function __construct(array $data) {
		parent::__construct();
		$this->_data = $data;
	}

	/**
	 * @return IDTO[]
	 */
	protected function getData():array{
		return $this->_data;
	}
}