<?php
namespace wfw\engine\package\news\domain;

/**
 * Lien vers le visuel d'un article
 */
class VisualLink {
	/** @var string $_link */
	private $_link;

	/**
	 * VisualLink constructor.
	 *
	 * @param string $link Lien du visuel de l'article
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $link) {
		if(strlen($link)>0) $this->_link = $link;
		else throw new \InvalidArgumentException("A VisualLink cann't be empty !");
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_link;
	}
}