<?php
namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Utilisateur confirmé
 */
final class UserConfirmedEvent extends UserEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 *  UserConfirmedEvent constructor.
	 *
	 * @param UUID      $aggregateId Identifiant de l'utilisateur
	 * @param UserState $state       Etat de l'utilisateur après sa confirmation
	 * @param string    $confirmerId Utilisateur ayant confirmé
	 */
	public function __construct(UUID $aggregateId,UserState $state,string $confirmerId) {
		parent::__construct($aggregateId,$confirmerId);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}