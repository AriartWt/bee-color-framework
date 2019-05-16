<?php
namespace wfw\engine\package\users\lib\mail;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailBody;
use wfw\engine\lib\network\mail\MailBody;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\command\errors\UserNotFound;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Email par défaut envoyé lorsqu'un utilisateur doit confirmer sa nouvelle adresse email.
 */
final class UserMailChangedMail extends AbstractUserMail{
	/**
	 * UserMailChangedMail constructor.
	 *
	 * @param UUID                 $userId
	 * @param UserConfirmationCode $code
	 * @param IConf                $conf
	 * @param ITranslator          $translator
	 * @param IUserModelAccess     $access
	 * @throws UserNotFound
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		UUID $userId,
		UserConfirmationCode $code,
		IConf $conf,
		ITranslator $translator,
		IUserModelAccess $access
	) {
		parent::__construct(
			$userId,
			$code,
			$conf,
			$access,
			$translator,
			new EmailSubject($translator->get(
				"server/engine/package/users/mail/subject/CONFIRM_MAIL_CHANGE"
			)),
			"changeMailConfirmation"
		);
	}

	/**
	 * @param User $user
	 * @param UserConfirmationCode $code
	 * @return IMailBody
	 */
	protected function createBody(User $user, UserConfirmationCode $code): IMailBody {
		$addr = $this->getDomain()."/users/changeMailConfirmation?id=".$user->getId()."&code=$code";
		$key="server/engine/package/users/mail";
		return new MailBody(
			$this->_translator->getAndReplace("$key/INTRO",$user->getLogin())
			.$this->_translator->getAndReplace("$key/explain/CONFIRM_MAIL_CHANGE",$addr)
			.$this->_translator->get("$key/WARN")
			.$this->_translator->get("$key/END")
			.($this->_translator->get("$key/SIGNATURE") ?? ""),
			null,
			false
		);
	}
}