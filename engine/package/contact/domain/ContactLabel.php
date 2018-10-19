<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/09/18
 * Time: 17:17
 */

namespace wfw\engine\package\contact\domain;

/**
 * Label d'une prise de contact
 */
class ContactLabel{
	/** @var string $_label */
	private $_label;

	/**
	 * ContactLabel constructor.
	 *
	 * @param string $label Nom du formulaire associé à la prise de contact
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $label) {
		if(empty($label)) throw new \InvalidArgumentException("A contact label can't be empty !");
		$this->_label=$label;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_label;
	}
}