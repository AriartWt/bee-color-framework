<?php
namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Utilisateur du MSServer
 */
interface IMSServerUser {
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