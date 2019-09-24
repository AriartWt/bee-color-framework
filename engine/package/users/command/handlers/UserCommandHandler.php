<?php
namespace wfw\engine\package\users\command\handlers;

use wfw\engine\core\command\ICommandHandler;
use wfw\engine\package\users\command\errors\UserNotFound;
use wfw\engine\package\users\domain\repository\IUserRepository;
use wfw\engine\package\users\domain\User;

/**
 * CommandHandler de base pour les utilisateurs
 */
abstract class UserCommandHandler implements ICommandHandler{
	/** @var IUserRepository $_repos */
	private $_repos;
	
	/**
	 * UserCommandHandler constructor.
	 * @param IUserRepository $repos
	 */
	public function __construct(IUserRepository $repos) {
		$this->_repos = $repos;
	}
	
	/**
	 * @param string $id Identifiant de l'utilisateur
	 * @return User
	 * @throws UserNotFound
	 */
	protected function get(string $id):User{
		$user = $this->_repos->get($id);
		if(is_null($user)) throw new UserNotFound($id);
		else return $user;
	}

	/**
	 * @param string[] $ids
	 * @return User[]
	 */
	protected function getAll(string... $ids):array{
		/** @var User[] $users */
		$users = $this->_repos->getAll(...$ids);
		if(count($ids)!==count($users)){
			$notFound = [];
			foreach($users as $u){
				if(!is_integer(array_search((string)$u->getId(),$ids)))
					$notFound[] = (string) $u->getId();
			}
			throw new UserNotFound(implode(',',$notFound));
		}else return $users;
	}
	
	/**
	 * @return IUserRepository
	 */
	protected function repos():IUserRepository{
		return $this->_repos;
	}
}