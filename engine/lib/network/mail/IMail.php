<?php
namespace wfw\engine\lib\network\mail;

/**
 * Represent an email.
 */
interface IMail {
	/**
	 * @return INamedEmail
	 */
	public function from():INamedEmail;
	
	/**
	 * @return INamedEmail[]
	 */
	public function to():array;
	
	/**
	 * @return INamedEmail[]
	 */
	public function cc():array;
	
	/**
	 * @return INamedEmail[]
	 */
	public function bcc():array;
	
	/**
	 * @return IMailAttachment[]
	 */
	public function attachments():array;
	
	/**
	 * @return IEmailSubject
	 */
	public function subject():IEmailSubject;
	
	/**
	 * @return IMailBody
	 */
	public function body():IMailBody;
	
	/**
	 * @return IReplyTo[]
	 */
	public function replyTo():array;
}