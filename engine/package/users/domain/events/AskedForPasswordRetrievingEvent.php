<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 02:01
 */

namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  Demande de réinitilisation de mot de passe
 */
final class AskedForPasswordRetrievingEvent extends ConfirmationEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 *  AskedForPasswordRetrievingEvent constructor.
	 *
	 * @param UUID                 $userId Identifiant de l'utilisateur
	 * @param UserConfirmationCode $code   Code de confirmation
	 * @param UserState            $state  Nouvel état de l'utilisateur
	 * @param string               $askerId Utilisateur ayant demandé la procédure de récupération
	 */
	public function __construct(UUID $userId, UserConfirmationCode $code,UserState $state,string $askerId)
	{
		parent::__construct($userId,$askerId,$code);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}