<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 16:05
 */

namespace wfw\daemons\rts\server\environment;

/**
 * Groupe d'utilisateur RTS
 */
interface IRTSUserGroup {
	/**
	 *  Retourne l'utilisateur du groupe dont le nom est $name
	 *
	 * @param string $name Nom de l'utilisateur.
	 *
	 * @return IRTSUser
	 */
	public function getUser(string $name):IRTSUser;

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