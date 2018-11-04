<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\IMailFactory;
use wfw\engine\package\users\command\RetrievePassword;
use wfw\engine\package\users\domain\repository\IUserRepository;
use wfw\engine\package\users\lib\confirmationCode\IUserConfirmationCodeGenerator;
use wfw\engine\package\users\lib\mail\IUserRegisteredMail;

/**
 * Gère le lancement d'une procédure de récupération de mot de passe
 */
final class RetrievePasswordHandler extends UserCommandHandler{
	/** @var IMailProvider $_mailProvider */
	private $_mailProvider;
	/** @var IMailFactory $_mailFactory */
	private $_mailFactory;
	/** @var IUserConfirmationCodeGenerator $_generator */
	private $_generator;

	/**
	 * RetrievePasswordHandler constructor.
	 * @param IUserRepository $repos
	 * @param IMailProvider $mailProvider
	 * @param IMailFactory $mailFactory
	 * @param IUserConfirmationCodeGenerator $generator
	 */
	public function __construct(
		IUserRepository $repos,
		IMailProvider $mailProvider,
		IMailFactory $mailFactory,
		IUserConfirmationCodeGenerator $generator
	){
		parent::__construct($repos);
		$this->_mailProvider = $mailProvider;
		$this->_mailFactory = $mailFactory;
		$this->_generator = $generator;
	}

	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command){
		/** @var RetrievePassword $command */
		$user = $this->get($command->getUserId());
		$confirmationCode = $this->_generator->createUserConfirmationCode();
		$user->retrievePassword($confirmationCode,$command->getAskerId());
		if(is_null($command->getPassword())){
			$this->_mailProvider->send($this->_mailFactory->create(
				IUserRegisteredMail::class,
				[
					$user->getId(),
					$confirmationCode
				]
			));
		}else{
			$user->resetPassword(
				$command->getPassword(),
				$confirmationCode,
				$command->getAskerId(),
				$command->getState()
			);
		}
		$this->repos()->modify($user,$command);
	}
}