<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 01:35
 */

namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\states\UserState;

/**
 *  Evenemnt d'annulation de changement d'adresse email
 */
final class CanceledUserMailChangeEvent extends UserEvent{
	/** @var UserState $_state */
	private $_state;
	
	/**
	 * CanceledUserMailChangeEvent constructor.
	 *
	 * @param UUID      $aggregateId Identifiant de l'utilisateur
	 * @param UserState $state       Nouvel etat de l'utilisateur
	 * @param string    $canclerId   Identifiant de l'utilisateur ayant demandé l'annulation
	 */
	public function __construct(UUID $aggregateId,UserState $state,string $canclerId) {
		parent::__construct($aggregateId,$canclerId);
		$this->_state = $state;
	}

	/**
	 * @return UserState
	 */
	public function getUserState():UserState{
		return $this->_state;
	}
}