<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 15:54
 */

namespace wfw\engine\lib\network\mail\providers;

use PHPMailer\PHPMailer\PHPMailer;
use wfw\engine\lib\network\mail\IMail;
use wfw\engine\lib\network\mail\IMailProvider;

/**
 * permet d'envoyer un mail via la librairie PHPMailer
 */
final class PHPMailerProvider implements IMailProvider{
	/** @var PHPMailer $_mailer */
	private $_mailer;
	/** @var bool $_DKIMEnabled */
	private $_DKIMEnabled;

	/**
	 * PHPMailerProvider constructor.
	 *
	 * @param PHPMailer $mailer Instance préconfigurée pour un envoi (DKIM, SMTP, ...)
	 * @param bool      $dkimEnabled True if DKIM is enabled. ( cause DKIM identity to be set to
	 *                               $mail->from() on each send() call. To avoid it, set dkimEnabled
	 *                               to false and set DKIM_identity to the proper identity before
	 *                               pass it to this constructor.)
	 */
	public function __construct(PHPMailer $mailer,bool $dkimEnabled = true) {
		$this->_mailer = $mailer;
		$this->_DKIMEnabled = $dkimEnabled;
	}
	
	/**
	 * @param IMail $mail Mail à envoyer
	 */
	public function send(IMail $mail) {
		$this->_mailer->clearReplyTos();
		$this->_mailer->clearAttachments();
		$this->_mailer->clearAllRecipients();
		
		$this->_mailer->setFrom($mail->from()->mail(),$mail->from()->name());
		if($this->_DKIMEnabled) $this->_mailer->DKIM_identity = $mail->from()->mail();
		
		foreach($mail->to() as $ne){
			$this->_mailer->addAddress($ne->mail(),$ne->name());
		}
		foreach($mail->cc() as $ne){
			$this->_mailer->addCC($ne->mail(),$ne->name());
		}
		foreach($mail->bcc() as $ne){
			$this->_mailer->addBCC($ne->mail(),$ne->name());
		}
		foreach($mail->replyTo() as $ne){
			$this->_mailer->addReplyTo($ne->mail(),$ne->subject());
		}
		foreach($mail->attachments() as $att){
			$this->_mailer->addAttachment(
				$att->path(),
				$att->name(),
				$att->encoding() ?? PHPMailer::ENCODING_BASE64,
				$att->type() ?? '',
				$att->disposition() ?? "attachment"
			);
		}
		$this->_mailer->CharSet = 'UTF-8';
		$this->_mailer->Subject = (string) $mail->subject();
		$this->_mailer->Body = (string) $mail->body();
		$this->_mailer->AltBody = $mail->body()->alt();
		$this->_mailer->isHTML($mail->body()->isHTML());
		ob_start();
		$this->_mailer->send();
		ob_end_clean();
	}
}