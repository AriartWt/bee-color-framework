<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 16:06
 */

namespace wfw\engine\lib\network\mail;

use wfw\engine\package\general\domain\Email;

/**
 * Class ReplyTo
 *
 * @package wfw\engine\lib\network\mail
 */
final class ReplyTo implements IReplyTo {
	/** @var Email $_addr */
	private $_addr;
	/** @var EmailSubject $_subject */
	private $_subject;
	
	/**
	 * ReplyTo constructor.
	 *
	 * @param Email        $addr    Address
	 * @param EmailSubject $subject Subject
	 */
	public function __construct(Email $addr, EmailSubject $subject) {
		$this->_addr = $addr;
		$this->_subject = $subject;
	}
	
	/**
	 * @return Email
	 */
	public function mail():Email{
		return $this->_addr;
	}
	
	/**
	 * @return EmailSubject
	 */
	public function subject():EmailSubject{
		return $this->_subject;
	}
}