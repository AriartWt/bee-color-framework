<?php
namespace wfw\engine\package\users\lib\mail;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailBody;
use wfw\engine\lib\network\mail\MailBody;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Class UserRegisteredMail
 * @package wfw\engine\package\users\lib\mail
 */
final class UserResetPasswordMail extends AbstractUserMail{
	/**
	 * UserMailChangedMail constructor.
	 *
	 * @param UUID                 $userId
	 * @param UserConfirmationCode $code
	 * @param IConf                $conf
	 * @param IUserModelAccess     $access
	 * @param ITranslator          $translator
	 * @throws \InvalidArgumentException
	 * @throws \wfw\engine\package\users\command\errors\UserNotFound
	 */
	public function __construct(
		UUID $userId,
		UserConfirmationCode $code,
		IConf $conf,
		IUserModelAccess $access,
		ITranslator $translator
	){
		parent::__construct(
			$userId,
			$code,
			$conf,
			$access,
			$translator,
			new EmailSubject($translator->get(
				"server/engine/package/users/mail/subject/RESET_PASSWORD"
			)),
			"resetPassword"
		);
	}

	/**
	 * @param User $user
	 * @param UserConfirmationCode $code
	 * @return IMailBody
	 */
	protected function createBody(User $user, UserConfirmationCode $code): IMailBody {
		$addr = $this->getDomain()."/users/resetPassword?id=".$user->getId()."&code=$code";
		$key="server/engine/package/users/mail";
		return new MailBody(
			$this->_translator->getAndReplace("$key/INTRO",$user->getLogin())
			.$this->_translator->getAndReplace("$key/explain/RESET_PASSWORD",$addr)
			.$this->_translator->get("$key/WARN")
			.$this->_translator->get("$key/END")
			.($this->_translator->get("$key/SIGNATURE") ?? ""),
			null,
			false
		);
	}
}