<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 14:50
 */

namespace wfw\engine\lib\network\mail;

/**
 * Gère la partie envoie du mail
 */
interface IMailProvider {
	/**
	 * @param IMail $mail Mail à envoyer
	 */
	public function send(IMail $mail);
}