<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 01:12
 */

namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Nouvel email confirmé
 */
final class UserMailConfirmedEvent extends UserEvent
{
	/** @var Email $_email */
	private $_email;
	/** @var UserState $_state */
	private $_state;
	
	/**
	 *  UserMailConfirmedEvent constructor.
	 *
	 * @param UUID      $userId Identifiant de l'utilisateur
	 * @param Email     $email  Nouvel email
	 * @param UserState $state  Nouvel etat de l'utilisateur
	 * @param string    $confirmerId Identifiant de l'utilisateur confirmant la modification
	 */
	public function __construct(UUID $userId, Email $email, UserState $state,string $confirmerId) {
		parent::__construct($userId,$confirmerId);
		$this->_email = $email;
		$this->_state = $state;
	}

	/**
	 * @return Email
	 */
	public function getEmail():Email{
		return $this->_email;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}