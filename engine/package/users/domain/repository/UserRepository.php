<?php
namespace wfw\engine\package\users\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\repository\IAggregateRootRepository;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\User;

/**
 * Repository pour les utilisateurs
 */
final class UserRepository implements IUserRepository {
	/** @var IAggregateRootRepository $_repos */
	private $_repos;

	/**
	 * UserRepository constructor.
	 *
	 * @param IAggregateRootRepository $repos
	 */
	public function __construct(IAggregateRootRepository $repos) {
		$this->_repos = $repos;
	}

	/**
	 * @param string $id Identifiant de l'utilisateur
	 *
	 * @return null|User
	 */
	public function get(string $id): ?User {
		/** @var User $user */
		$user = $this->_repos->get(new UUID(UUID::V6,$id));
		return $user;
	}
	
	/**
	 * Ajoute ou modifie un utilisateur
	 *
	 * @param User          $user Utilisateur
	 * @param null|ICommand $cmd Commande
	 */
	public function add(User $user,?ICommand $cmd = null): void {
		$this->_repos->add($user);
	}
	
	/**
	 * @param User          $user Utilisateur à supprimer
	 * @param null|ICommand $cmd Commande
	 */
	public function remove(User $user,?ICommand $cmd = null): void {
		$this->_repos->remove($user);
	}
	
	/**
	 * Modifie l'utilisateur dans le repository
	 *
	 * @param User          $user utilisateur modifié
	 * @param null|ICommand $cmd Commande
	 */
	public function modify(User $user,?ICommand $cmd = null): void {
		$this->_repos->modify($user);
	}
	
	/**
	 * @param string ...$ids Identifiants des utilisateurs
	 * @return array
	 */
	public function getAll(string... $ids): array {
		$uuids = [];
		foreach ($ids as $id){$uuids[] = new UUID(UUID::V6,$id);}
		return $this->_repos->getAll(...$uuids);
	}
	
	/**
	 * Ajoute de sutilisateurs au repository
	 *
	 * @param null|ICommand $cmd Commande
	 * @param User          ...$users Utilisateurs à ajouter
	 */
	public function addAll(?ICommand $cmd = null,User... $users): void {
		$this->_repos->addAll($cmd,...$users);
	}
	
	/**
	 * Modifie des utilisateurs dans le repository
	 *
	 * @param null|ICommand $cmd Commande
	 * @param User          ...$users utilisateurs à modifier
	 */
	public function modifyAll(?ICommand $cmd = null,User... $users): void {
		$this->_repos->modifyAll($cmd,...$users);
	}
	
	/**
	 * @param null|ICommand $cmd Commande
	 * @param User          ...$users utilisateurs à supprimer
	 */
	public function removeAll(?ICommand $cmd = null,User... $users): void {
		$this->_repos->removeAll($cmd,...$users);
	}
}