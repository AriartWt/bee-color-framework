<?php
namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 *  Evenement demandant un code de confirmation
 */
abstract class ConfirmationEvent extends UserEvent {
	/** @var UserConfirmationCode $_code */
	private $_code;
	
	/**
	 *  ConfirmationEvent constructor.
	 *
	 * @param UUID                 $userId Identifiant de l'utilisateur
	 * @param string               $modifierId identifiant de l'utilisateur ayant demandé la confirmation
	 * @param UserConfirmationCode $code   Code de confirmation
	 */
	public function __construct(UUID $userId,string $modifierId,UserConfirmationCode $code) {
		parent::__construct($userId,$modifierId);
		$this->_code = $code;
	}

	/**
	 * @return UserConfirmationCode
	 */
	public function getCode():UserConfirmationCode{
		return $this->_code;
	}
}