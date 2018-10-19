<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 17:00
 */

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