<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 22/01/18
 * Time: 09:07
 */

namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Groupe d'utilisateurs du MSServer
 */
interface IMSServerUserGroup
{
	/**
	 *  Retourne l'utilisateur du groupe dont le nom est $name
	 * @param string $name Nom de l'utilisateur.
	 *
	 * @return IMSServerUser
	 */
	public function getUser(string $name):IMSServerUser;

	/**
	 * @return IKVSUser[] Liste des utilisateurs appartenant au groupe.
	 */
	public function getUsers():array;

	/**
	 *  Vérifie la présence d'un utilisateur dans le groupe
	 * @param string $name Nom de l'utilisateur à tester
	 *
	 * @return bool
	 */
	public function hasUser(string $name):bool;

	/**
	 * @return string Nom du groupe
	 */
	public function getName():string;
}