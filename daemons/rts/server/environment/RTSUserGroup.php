<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 16:47
 */

namespace wfw\daemons\rts\server\environment;

use wfw\daemons\rts\server\errors\UserNotFound;

/**
 * Groupe d'utilisateurs RTS
 */
final class RTSUserGroup implements IRTSUserGroup{
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
			if($user instanceof IRTSUser){
				$this->_users[$user->getName()] = $user;
			}else{
				throw new \InvalidArgumentException("Invalid object at offset $k : ".IRTSUser::class." expected but ".((new Type($user))->get()." given !"));
			}
		}
	}

	/**
	 *  Retourne l'utilisateur du groupe dont le nom est $name
	 *
	 * @param string $name Nom de l'utilisateur.
	 *
	 * @return IRTSUser
	 * @throws UserNotFound
	 */
	public function getUser(string $name): IRTSUser {
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