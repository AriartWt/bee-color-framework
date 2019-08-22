<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 16:04
 */

namespace wfw\daemons\rts\server\environment;

/**
 * Utilisateur du RTS (local)
 */
interface IRTSUser{
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