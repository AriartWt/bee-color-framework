<?php
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