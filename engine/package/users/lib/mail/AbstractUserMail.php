<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 01/07/18
 * Time: 11:45
 */

namespace wfw\engine\package\users\lib\mail;

use wfw\engine\core\conf\IConf;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailBody;
use wfw\engine\lib\network\mail\Mail;
use wfw\engine\lib\network\mail\NamedEmail;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\command\errors\UserNotFound;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Class AbstractUserMail
 * @package wfw\engine\package\users\lib\mail
 */
abstract class AbstractUserMail extends Mail{
	/**
	 * UserMailChangedMail constructor.
	 * @param UUID $userId
	 * @param UserConfirmationCode $code
	 * @param IConf $conf
	 * @param IUserModelAccess $access
	 * @param string $configkeyName
	 * @throws UserNotFound
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		UUID $userId,
		UserConfirmationCode $code,
		IConf $conf,
		IUserModelAccess $access,
		string $configkeyName
	){
		$user = $access->getById($userId);
		if(is_null($user)) throw new UserNotFound($userId);
		parent::__construct(
			new NamedEmail(
				new Email(
					$conf->getString("server/mailer/addrs/$configkeyName/addr")
					?? $conf->getString("server/mailer/addrs/default/addr")
				),
				$conf->getString("server/mailer/addrs/$configkeyName/name")
				?? $conf->getString("server/mailer/addrs/default/name")
			),
			[
				new NamedEmail($user->getEmail())
			],
			[],
			[],
			[],
			[],
			new EmailSubject("Confirmation de votre nouvelle adresse email"),
			$this->createBody($user,$code)
		);
	}

	/**
	 * @param User $user
	 * @param UserConfirmationCode $code
	 * @return IMailBody
	 */
	protected abstract function createBody(User $user, UserConfirmationCode $code):IMailBody;
}