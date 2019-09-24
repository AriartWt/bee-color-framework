<?php
namespace wfw\engine\lib\network\mail;

/**
 * Représente un mail à envoyer
 */
class Mail implements IMail{
	/** @var INamedEmail $_from */
	private $_from;
	/** @var INamedEmail[] $_to */
	private $_to;
	/** @var INamedEmail[] $_cc */
	private $_cc;
	/** @var INamedEmail[] $_bcc */
	private $_bcc;
	/** @var IMailAttachment[] $_attachments */
	private $_attachments;
	/** @var IReplyTo[] $_replyTo */
	private $_replyTo;
	/** @var IEmailSubject $_subject */
	private $_subject;
	/** @var IMailBody $_body */
	private $_body;
	
	/**
	 * Mail constructor.
	 *
	 * @param INamedEmail       $from
	 * @param INamedEmail[]     $to
	 * @param INamedEmail[]     $cc
	 * @param INamedEmail[]     $bcc
	 * @param IMailAttachment[] $attachments
	 * @param IReplyTo[]    $replyTo
	 * @param IEmailSubject $subject
	 * @param IMailBody     $body
	 */
	public function __construct(
		INamedEmail $from,
		array $to,
		array $cc,
		array $bcc,
		array $attachments,
		array $replyTo,
		IEmailSubject $subject,
		IMailBody $body
	){
		$this->_from = $from;
		$this->_to = $this->checkNamedEmails(...$to);
		$this->_cc = $this->checkNamedEmails(...$cc);
		$this->_bcc = $this->checkNamedEmails(...$bcc);
		$this->_attachments = (function(IMailAttachment... $attachments){
			return $attachments;
		})(...$attachments);
		$this->_replyTo = (function(IReplyTo... $replyTos){
			return $replyTos;
		})(...$replyTo);
		$this->_subject = $subject;
		$this->_body = $body;
	}
	
	/**
	 * @param INamedEmail[] $mails
	 * @return array
	 */
	private function checkNamedEmails(INamedEmail... $mails):array{
		return $mails;
	}
	
	/**
	 * @return INamedEmail
	 */
	public function from(): INamedEmail {
		return $this->_from;
	}
	
	/**
	 * @return INamedEmail[]
	 */
	public function to(): array {
		return $this->_to;
	}
	
	/**
	 * @return INamedEmail[]
	 */
	public function cc(): array {
		return $this->_cc;
	}
	
	/**
	 * @return INamedEmail[]
	 */
	public function bcc(): array {
		return $this->_bcc;
	}
	
	/**
	 * @return IMailAttachment[]
	 */
	public function attachments(): array {
		return $this->_attachments;
	}
	
	/**
	 * @return IEmailSubject
	 */
	public function subject(): IEmailSubject {
		return $this->_subject;
	}
	
	/**
	 * @return IMailBody
	 */
	public function body(): IMailBody {
		return $this->_body;
	}
	
	/**
	 * @return IReplyTo[]
	 */
	public function replyTo(): array {
		return $this->_replyTo;
	}
}