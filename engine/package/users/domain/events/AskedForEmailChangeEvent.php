<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 01:19
 */

namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  Demande de modification d'adresse mail
 */
final class AskedForEmailChangeEvent extends ConfirmationEvent {
	/** @var Email $_email */
	private $_email;
	/** @var UserState $_state */
	private $_state;
	
	/**
	 *  AskedForEmailChangeEvent constructor.
	 *
	 * @param UUID                 $userId Id de l'utilisateur
	 * @param Email                $email  Nouvel email
	 * @param UserConfirmationCode $code   Code de confirmation
	 * @param UserState            $state  Etat de l'utilisateur
	 * @param string               $modifierId Identifiant de l'utilisateur ayant demandé le reset
	 */
	public function __construct(
		UUID $userId,
		Email $email,
		UserConfirmationCode $code,
		UserState $state,
		string $modifierId
	){
		parent::__construct($userId,$modifierId,$code);
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