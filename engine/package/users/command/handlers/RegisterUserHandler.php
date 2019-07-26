<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\lib\network\mail\IMailFactory;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\command\errors\UserAlreadyExists;
use wfw\engine\package\users\command\RegisterUser;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\repository\IUserRepository;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;
use wfw\engine\package\users\domain\User;
use wfw\engine\package\users\lib\mail\IUserRegisteredMail;

/**
 * Traite une commande RegisterUser
 */
final class RegisterUserHandler extends UserCommandHandler{
	/** @var IMailProvider $_mailProvider */
	private $_mailProvider;
	/** @var IMailFactory $_mailFactory */
	private $_mailFactory;
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * RegisterUserHandler constructor.
	 * @param IUserRepository $repos
	 * @param IMailProvider $mailProvider
	 * @param IMailFactory $mailFactory
	 * @param IUserModelAccess $access
	 */
	public function __construct(
		IUserRepository $repos,
		IMailProvider $mailProvider,
		IMailFactory $mailFactory,
		IUserModelAccess $access
	){
		parent::__construct($repos);
		$this->_access = $access;
		$this->_mailFactory = $mailFactory;
		$this->_mailProvider = $mailProvider;
	}

	/**
	 * Traite la commande
	 *
	 * @param ICommand $command Commande à traiter
	 */
	public function handleCommand(ICommand $command) {
		/** @var RegisterUser $command */
		if(!is_null($this->_access->getByLogin($command->getLogin())))
			throw new UserAlreadyExists($command->getLogin()." n'est pas un login disponible");

		$this->repos()->add(new User(
			new UUID(),
			$command->getLogin(),
			$command->getPassword(),
			$command->getEmail(),
			$command->getSettings(),
			$command->getState(),
			$command->getType(),
			$command->getCreator()
		),$command);
		$state = $command->getState();
		if($state instanceof UserWaitingForRegisteringConfirmation){
			$this->_mailProvider->send($this->_mailFactory->create(
				IUserRegisteredMail::class,
				[
					$state->getCode(),
					$command->getCreator()
				]
			));
		}
	}
}