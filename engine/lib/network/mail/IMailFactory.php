<?php
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