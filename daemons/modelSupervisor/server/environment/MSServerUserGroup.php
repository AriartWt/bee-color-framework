<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/01/18
 * Time: 06:19
 */

namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\daemons\modelSupervisor\server\errors\UserNotFound;
use wfw\engine\lib\PHP\types\Type;

/**
 *  Groupe d'utilisateurs du MSServer
 */
final class MSServerUserGroup implements IMSServerUserGroup {
	/** @var IMSServerUser[] $_users */
	private $_users;
	/** @var string $_name */
	private $_name;

	/**
	 * MSServerUserGroup constructor.
	 *
	 * @param string                  $name  Nom du groupe
	 * @param IMSServerUser[] $users Utilisateurs du groupe
	 */
	public function __construct(string $name, array $users) {
		$this->_name = $name;
		$this->_users = [];
		foreach($users as $k=>$user){
			if($user instanceof IMSServerUser){
				$this->_users[$user->getName()] = $user;
			}else{
				throw new \InvalidArgumentException("Invalid object at offset $k : ".IMSServerUser::class." expected but ".((new Type($user))->get()." given !"));
			}
		}
	}

	/**
	 *  Retourne l'utilisateur du groupe dont le nom est $name
	 *
	 * @param string $name Nom de l'utilisateur.
	 *
	 * @return IMSServerUser
	 */
	public function getUser(string $name): IMSServerUser {
		if($this->hasUser($name)){
			return $this->_users[$name];
		}else{
			throw new UserNotFound("Unknown user $name");
		}
	}

	/**
	 * @return IMSServerUser[] Liste des utilisateurs appartenant au groupe.
	 */
	public function getUsers(): array {
		return array_values($this->_users);
	}

	/**
	 *  Vérifie la présence d'un utilisateur dans le groupe
	 *
	 * @param string $name Nom de l'utilisateur à tester
	 *
	 * @return bool
	 */
	public function hasUser(string $name): bool {
		return isset($this->_users[$name]);
	}

	/**
	 * @return string Nom du groupe
	 */
	public function getName(): string {
		return $this->_name;
	}
}