<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 09:18
 */

namespace wfw\daemons\rts\client;

/**
 * Permet de trouver l'addresse d'une instance RTS
 */
interface IRTSAddrResolver {
	/**
	 * Permet de retrouver l'adresse de la socket d'écoute d'une instance de MSServer
	 * @param string $name Nom de l'instance du MSServer
	 * @return string
	 */
	public function find(string $name):string;
}