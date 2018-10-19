<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/07/18
 * Time: 11:12
 */

namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\command\ChangeLogin;
use wfw\engine\package\users\command\errors\UserAlreadyExists;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\repository\IUserRepository;

/**
 * Gère la commande de changement de mot de passe
 */
final class ChangeLoginHandler extends UserCommandHandler{
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * ChangeLoginHandler constructor.
	 * @param IUserRepository $repos
	 * @param IUserModelAccess $access
	 */
	public function __construct(IUserRepository $repos,IUserModelAccess $access) {
		parent::__construct($repos);
		$this->_access = $access;
	}

	/**
	 * Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command) {
		/** @var ChangeLogin $command */
		if(is_null($this->_access->getByLogin($command->getLogin()))){
			$user = $this->get($command->getUserId());
			$user->changeLogin($command->getLogin(),$command->getModifierId());
			$this->repos()->modify($user,$command);
		}else{
			throw new UserAlreadyExists($command->getLogin()." is not available !");
		}
	}
}