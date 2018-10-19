<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/07/18
 * Time: 11:18
 */

namespace wfw\engine\package\general\domain;

/**
 * Class City
 * @package wfw\engine\package\general\domain
 */
class City {
	/** @var string $_city */
	private $_city;

	/**
	 * City constructor.
	 * @param string $name
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $name) {
		if(empty($name)) throw new \InvalidArgumentException("A city name cann't be empty !");
		$this->_city = $name;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_city;
	}
}