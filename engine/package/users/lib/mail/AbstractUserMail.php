<?php
namespace wfw\engine\package\users\lib\mail;

use wfw\engine\core\conf\IConf;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IEmailSubject;
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
	/** @var IConf $_conf */
	protected $_conf;
	/** @var ITranslator $_translator */
	protected $_translator;

	/**
	 * UserMailChangedMail constructor.
	 *
	 * @param UUID                 $userId
	 * @param UserConfirmationCode $code
	 * @param IConf                $conf
	 * @param IUserModelAccess     $access
	 * @param ITranslator          $translator
	 * @param IEmailSubject        $subject
	 * @param string               $configkeyName
	 * @throws UserNotFound
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		UUID $userId,
		UserConfirmationCode $code,
		IConf $conf,
		IUserModelAccess $access,
		ITranslator $translator,
		IEmailSubject $subject,
		string $configkeyName
	){
		$this->_conf = $conf;
		$this->_translator = $translator;
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
			$subject,
			$this->createBody($user,$code)
		);
	}

	/**
	 * @return string
	 */
	protected function getDomain():string{
		return $this->_conf->getString("server/domain") ?? "http://localhost";
	}

	/**
	 * @param User $user
	 * @param UserConfirmationCode $code
	 * @return IMailBody
	 */
	protected abstract function createBody(User $user, UserConfirmationCode $code):IMailBody;
}