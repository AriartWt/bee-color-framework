<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:36
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\lib\network\mail\IMailFactory;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\package\users\command\ChangeUserMail;
use wfw\engine\package\users\domain\repository\IUserRepository;
use wfw\engine\package\users\lib\confirmationCode\IUserConfirmationCodeGenerator;
use wfw\engine\package\users\lib\mail\IUserMailChangedMail;

/**
 * Applique la commande de changement de mail d'un utilisateur
 */
final class ChangeUserMailHandler extends UserCommandHandler {
	/** @var IMailProvider $_mailProvider */
	private $_mailProvider;
	/** @var IMailFactory $_mailFactory */
	private $_mailFactory;
	/** @var IUserConfirmationCodeGenerator $_generator */
	private $_generator;

	/**
	 * ChangeUserMailHandler constructor.
	 * @param IUserRepository $repos
	 * @param IMailProvider $provider
	 * @param IMailFactory $factory
	 * @param IUserConfirmationCodeGenerator $generator
	 */
	public function __construct(
		IUserRepository $repos,
		IMailProvider $provider,
		IMailFactory $factory,
		IUserConfirmationCodeGenerator $generator
	){
		parent::__construct($repos);
		$this->_mailProvider = $provider;
		$this->_mailFactory = $factory;
		$this->_generator = $generator;
	}

	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var ChangeUserMail $command */
		$user = $this->get($command->getUserId());
		$code = $this->_generator->createUserConfirmationCode();
		$user->changeEmail($command->getMail(),$code,$command->getModifier());
		if($command->sendMail()){
			$this->_mailProvider->send($this->_mailFactory->create(
				IUserMailChangedMail::class,
				[
					$user->getId(),
					$code
				]
			));
		}else{
			$user->confirmEmail(
				$code,
				$command->getModifier(),
				$command->getState()
			);
		}
		$this->repos()->modify($user,$command);
	}
}