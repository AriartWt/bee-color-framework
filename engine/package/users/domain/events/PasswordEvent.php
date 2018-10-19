<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 02:18
 */

namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\Password;

/**
 *  Evenement concernant le mot de passe d'un utilisateur
 */
abstract class PasswordEvent extends UserEvent
{
	/** @var Password $_password */
	private $_password;
	
	/**
	 *  PasswordEvent constructor.
	 *
	 * @param UUID     $userId   identifiant de l'utilisateur
	 * @param Password $password Mot de passe
	 * @param string   $modifierId Identifiant de l'utilisateur ayant demandé la modification
	 */
	public function __construct(UUID $userId, Password $password, string $modifierId)
	{
		parent::__construct($userId,$modifierId);
		$this->_password = $password;
	}

	/**
	 * @return Password
	 */
	public function getPassword():Password{
		return $this->_password;
	}
}