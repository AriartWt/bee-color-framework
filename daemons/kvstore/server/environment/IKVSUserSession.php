<?php
namespace wfw\daemons\kvstore\server\environment;

/**
 *  Session KVS
 */
interface IKVSUserSession {
	/**
	 * @return string Identifiant de la session
	 */
	public function getId():string;

	/**
	 * @return IKVSUser Utilisateur associé à la session
	 */
	public function getUser():IKVSUser;

	/**
	 * @return IKVSContainer Container auquel l'utilisateur est connecté.
	 */
	public function getContainer():IKVSContainer;

	/**
	 * @return int Mode de stockage des données par défaut
	 */
	public function getDefaultStorageMode():int;
}