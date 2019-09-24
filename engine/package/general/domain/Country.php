<?php
namespace wfw\engine\package\general\domain;

/**
 * Pays
 */
class Country {
	/** @var string $_country  */
	private $_country;

	/**
	 * Country constructor.
	 * @param string $country
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $country) {
		if(empty($country)) throw new \InvalidArgumentException("A country name cann't be empty !");
		$this->_country = $country;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_country;
	}
}