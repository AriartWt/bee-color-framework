<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 17:02
 */

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