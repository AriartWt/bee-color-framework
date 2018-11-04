<?php
namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Annule la réinitialisation du mot de passe
 */
final class UserPasswordRetrievingCanceledEvent extends UserEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 * UserPasswordRetrievingCanceledEvent constructor.
	 *
	 * @param UUID      $aggregateId Identifiant de l'utilisateur
	 * @param UserState $state       Nouvel état de l'utilisateur
	 * @param string    $cancelerId  Utilisateur demandant l'annulation de la procédure
	 */
	public function __construct(UUID $aggregateId,UserState $state,string $cancelerId) {
		parent::__construct($aggregateId,$cancelerId);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}