<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 16:04
 */

namespace wfw\engine\lib\network\mail;

/**
 * Permet de créer
 */
interface IMailFactory{
	/**
	 * @param string $class Mail à créer
	 * @param array $args Arguments à passer au constructeur du mail
	 * @return IMail
	 */
	public function create(string $class,array $args=[]):IMail;
}