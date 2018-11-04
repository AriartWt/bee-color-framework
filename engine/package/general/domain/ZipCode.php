<?php
namespace wfw\engine\package\general\domain;

/**
 * Code postal
 */
class ZipCode {
	/** @var string $_code */
	private $_code;

	/**
	 * ZipCode constructor.
	 * @param string $code
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $code) {
		if(empty($code)) throw new \InvalidArgumentException("A zipCode can't be empty !");
		$this->_code = $code;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_code;
	}
}