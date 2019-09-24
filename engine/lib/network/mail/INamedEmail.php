<?php
namespace wfw\engine\lib\network\mail;

use wfw\engine\package\general\domain\Email;


/**
 * Adresse mail associée à un nom
 */
interface INamedEmail {
	/**
	 * @return Email
	 */
	public function mail(): Email;
	
	/**
	 * @return null|string
	 */
	public function name(): ?string;
}