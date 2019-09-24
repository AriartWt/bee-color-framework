<?php
namespace wfw\engine\lib\network\mail;

/**
 * Objet d'un email.
 */
final class EmailSubject implements IEmailSubject {
	/** @var string $_subject */
	private $_subject;
	
	/**
	 * EmailSubject constructor.
	 *
	 * @param string $subject Objet
	 */
	public function __construct(string $subject) {
		$this->_subject = strip_tags(str_replace(["\n","\r"], "", $subject));
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_subject;
	}
}