<?php
namespace wfw\daemons\kvstore\server\environment;

/**
 *  Utilisateur KVS
 */
interface IKVSUser {
	/**
	 * @return string Nom de l'utilisateur
	 */
	public function getName():string;

	/**
	 *  Teste la validité d'un mot de passe.
	 *
	 * @param string $password Mot de passe à tester
	 *
	 * @return bool
	 */
	public function matchPassword(string $password):bool;
}