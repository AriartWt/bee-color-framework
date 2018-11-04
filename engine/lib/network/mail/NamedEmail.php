<?php
namespace wfw\engine\lib\network\mail;

use wfw\engine\package\general\domain\Email;

/**
 * Adresse mail associée à un nom
 */
final class NamedEmail implements INamedEmail {
	/** @var Email $_mail */
	private $_mail;
	/** @var null|string $_name */
	private $_name;
	
	/**
	 * NamedEmail constructor.
	 *
	 * @param Email       $mail Mail
	 * @param null|string $name Nom
	 */
	public function __construct(Email $mail, ?string $name = null) {
		$this->_mail = $mail;
		$this->_name = $name;
	}
	
	/**
	 * @return Email
	 */
	public function mail():Email{
		return $this->_mail;
	}
	
	/**
	 * @return null|string
	 */
	public function name():?string{
		return $this->_name;
	}
}