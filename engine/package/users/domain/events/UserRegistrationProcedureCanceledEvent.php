<?php
namespace wfw\engine\package\users\domain\events;
use wfw\engine\lib\PHP\types\UUID;

/**
 * La procedure d'enregistrement de l'utilisateur est annulée.
 */
final class UserRegistrationProcedureCanceledEvent extends UserEvent{
	/**
	 * @var bool $_removeUser
	 */
	private $_removeUser;

	/**
	 * UserRegistrationProcedureCanceled constructor.
	 * @param UUID $aggregateId
	 * @param string $modifierId
	 * @param bool $removeUser
	 */
	public function __construct(UUID $aggregateId, string $modifierId,bool $removeUser=true) {
		parent::__construct($aggregateId, $modifierId);
		$this->_removeUser = $removeUser;
	}

	/**
	 * @return bool
	 */
	public function removeUser(): bool {
		return $this->_removeUser;
	}
}