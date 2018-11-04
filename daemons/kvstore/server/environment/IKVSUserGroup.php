<?php
namespace wfw\daemons\kvstore\server\environment;

/**
 *  Groupe d'utilisateur du KVS
 */
interface IKVSUserGroup {
	/**
	 *  Retourne l'utilisateur du groupe dont le nom est $name
	 * @param string $name Nom de l'utilisateur.
	 *
	 * @return IKVSUser
	 */
	public function getUser(string $name):IKVSUser;

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