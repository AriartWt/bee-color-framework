<?php
namespace wfw\engine\lib\network\mail;


/**
 * Objet d'un email.
 */
interface IEmailSubject {
	/**
	 * @return string
	 */
	public function __toString();
}