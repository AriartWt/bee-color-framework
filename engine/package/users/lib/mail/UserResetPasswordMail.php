<?php
namespace wfw\engine\package\users\lib\mail;

use wfw\engine\core\conf\IConf;
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
	 * @param UUID $userId
	 * @param UserConfirmationCode $code
	 * @param IConf $conf
	 * @param IUserModelAccess $access
	 * @throws UserNotFound
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		UUID $userId,
		UserConfirmationCode $code,
		IConf $conf,
		IUserModelAccess $access
	){
		parent::__construct(
			$userId,
			$code,
			$conf,
			$access,
			"resetPassword"
		);
	}

	/**
	 * @param User $user
	 * @param UserConfirmationCode $code
	 * @return IMailBody
	 */
	protected function createBody(User $user, UserConfirmationCode $code): IMailBody {
		return new MailBody(
			"https://bee-color.fr/users/resetPassword?id=".$user->getId()."&code=$code",
			null,
			false
		);
	}
}