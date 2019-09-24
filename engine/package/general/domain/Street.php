<?php
namespace wfw\engine\package\general\domain;

/**
 * Rue
 */
class Street {
	/** @var string $_street */
	private $_street;

	/**
	 * Street constructor.
	 * @param string $street Rue
	 */
	public function __construct(string $street) {
		if(empty($street)) throw new \InvalidArgumentException("A street can't be empty !");
		$this->_street = $street;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_street;
	}
}