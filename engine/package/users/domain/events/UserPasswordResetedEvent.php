<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 01:51
 */

namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Mot de passe réinitialisé
 */
final class UserPasswordResetedEvent extends PasswordEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 * UserPasswordResetedEvent constructor.
	 *
	 * @param UUID      $userId   identifiant de l'utilisateur
	 * @param Password  $password Nouveau mot de passe
	 * @param UserState $state    Nouvel état de l'utilisateur
	 * @param string    $reseterId
	 */
	public function __construct(UUID $userId, Password $password,UserState $state, string $reseterId) {
		parent::__construct($userId, $password,$reseterId);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}

}