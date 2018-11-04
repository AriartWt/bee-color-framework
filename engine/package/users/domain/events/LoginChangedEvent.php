<?php
namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\Login;

/**
 *  Evenement émi lors d'un changement de login
 */
final class LoginChangedEvent extends UserEvent {
	/** @var $_login */
	private $_login;
	
	/**
	 *  LoginChangedEvent constructor.
	 *
	 * @param UUID   $userId Identifiant de l'utilisateur
	 * @param Login  $login  Login
	 * @param string $modifierId Identifiant de l'utilisateur ayant demandé le changement
	 */
	public function __construct(UUID $userId, Login $login,string $modifierId) {
		parent::__construct($userId,$modifierId);
		$this->_login = $login;
	}

	/**
	 * @return Login
	 */
	public function getLogin():Login{
		return $this->_login;
	}
}