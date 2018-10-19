<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/12/17
 * Time: 09:09
 */

namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Place l'utilisateur dans l'état "supprimé"
 */
class UserRemovedEvent extends UserEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 * UserRemovedEvent constructor.
	 *
	 * @param UUID      $aggregateId Identifiant de l'utilisateur
	 * @param UserState $state       Nouvel état de l'utilisateur
	 * @param string    $removerId   identifiant de l'utilisateur ayant supprimé l'utilisateur courant.
	 */
	public function __construct(UUID $aggregateId,UserState $state, string $removerId) {
		parent::__construct($aggregateId,$removerId);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}