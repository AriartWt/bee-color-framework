<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 16:07
 */

namespace wfw\daemons\rts\server\environment;

/**
 * Session d'un utilisateur rts (local port)
 */
interface IRTSSession {
	/**
	 * @return string Identifiant de la session
	 */
	public function getId():string;

	/**
	 * @return IRTSUser Utilisateur associé à la session
	 */
	public function getUser():IRTSUser;
}