<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/04/18
 * Time: 10:57
 */

namespace wfw\engine\package\users\data\model;

use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\data\model\specs\LoginIs;

/**
 * Classe d'accés au model User
 */
final class UserModelAccess implements IUserModelAccess
{
	/** @var IMSServerAccess $_db */
	private $_db;

	/**
	 * UserModelAccess constructor.
	 *
	 * @param IMSServerAccess $access
	 */
	public function __construct(IMSServerAccess $access) {
		$this->_db = $access;
	}

	/**
	 * @param string $login Login de l'utilisateur à chercher
	 * @return null|User
	 * @throws \Exception
	 */
	public function getByLogin(string $login):?User{
		$login = new LoginIs($login);
		$res = $this->_db->query(UserModel::class,"$login");
		if(count($res) > 0) return $res[0];
		return null;
	}

	/**
	 * @param string $id Identifiant de l'utilisateur recherché
	 * @return null|User
	 */
	public function getById(string $id): ?User{
		$res = $this->_db->query(UserModel::class,"id='$id'");
		if(count($res)>0) return $res[0];
		return null;
	}

	/**
	 * @return User[]
	 */
	public function getAll():array{
		return $this->_db->query(UserModel::class,"id");
	}

	/**
	 * Ne retourne un utilisateur que si celui-ci est considéré comme activé
	 * @param string $login Login de l'utilisateur à chercher
	 * @return null|User
	 */
	public function getEnabledByLogin(string $login): ?User {
		$login = new LoginIs($login);
		$res = $this->_db->query(UserModel::class,"$login:".UserModel::IS_ENABLED);
		if(count($res) > 0) return $res[0];
		return null;
	}
}