<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/09/18
 * Time: 13:08
 */

namespace wfw\engine\package\contact\domain;

/**
 * Simples infos de contact sous forme d'une chaine de caractère non vide.
 */
final class ContactInfos implements IContactInfos{
	/** @var string $_infos */
	private $_infos;

	/**
	 * ContactInfos constructor.
	 *
	 * @param string $infos Information de contact
	 */
	public function __construct(string $infos) {
		if(empty($infos)) throw new \InvalidArgumentException("Contact infos can't be empty !");
		$this->_infos = $infos;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_infos;
	}
}