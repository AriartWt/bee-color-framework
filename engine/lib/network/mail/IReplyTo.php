<?php
namespace wfw\engine\lib\network\mail;

use wfw\engine\package\general\domain\Email;


/**
 * Class ReplyTo
 *
 * @package wfw\engine\lib\network\mail
 */
interface IReplyTo {
	/**
	 * @return Email
	 */
	public function mail(): Email;
	
	/**
	 * @return EmailSubject
	 */
	public function subject(): EmailSubject;
}