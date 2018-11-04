<?php
namespace wfw\engine\package\news\domain;

/**
 * Titre d'un article
 */
class Title {
	/** @var string $_title */
	private $_title;

	/**
	 * Title constructor.
	 *
	 * @param string $title
	 */
	public function __construct(string $title) {
		if(strlen($title)>0) $this->_title = $title;
		else throw new \InvalidArgumentException("An article title cann't be empty !");
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_title;
	}
}