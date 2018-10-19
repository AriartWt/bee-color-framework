<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 17/04/18
 * Time: 09:32
 */

namespace wfw\engine\package\users\data\model;

use wfw\engine\package\users\data\model\DTO\User;

/**
 * Acces au model utilisateur
 */
interface IUserModelAccess
{
	/**
	 * @param string $login Login de l'utilisateur à chercher
	 * @return null|User
	 * @throws \Exception
	 */
	public function getByLogin(string $login):?User;

	/**
	 * Ne retourne un utilisateur que si celui-ci est considéré comme activé
	 * @param string $login Login de l'utilisateur à chercher
	 * @return null|User
	 */
	public function getEnabledByLogin(string $login):?User;

	/**
	 * @param string $id Identifiant de l'utilisateur recherché
	 * @return null|User
	 */
	public function getById(string $id):?User;

	/**
	 * @return User[]
	 */
	public function getAll():array;
}