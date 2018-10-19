<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 16:53
 */

namespace wfw\engine\lib\network\mail;

/**
 * Corps de mail
 */
final class MailBody implements IMailBody{
	/** @var null|string $_alt */
	private $_alt;
	/** @var bool $_html */
	private $_html;
	/** @var string $_body */
	private $_body;
	
	/**
	 * MailBody constructor.
	 *
	 * @param string      $body   Contenu
	 * @param null|string $alt    Description alternative
	 * @param bool        $isHTML True si $body contient de l'HTML
 	 */
	public function __construct(string $body, ?string $alt = null, bool $isHTML = true) {
		$this->_body = $body;
		$this->_alt = $alt;
		$this->_html = $isHTML;
	}
	
	/**
	 * @return bool
	 */
	public function isHTML(): bool {
		return $this->_html;
	}
	
	/**
	 * @return string
	 */
	public function alt(): string {
		return $this->_alt??'';
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_body;
	}
}