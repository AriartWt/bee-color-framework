<?php
namespace wfw\engine\package\contact\domain\events;

use wfw\engine\core\domain\events\DomainEvent;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Contact event
 */
abstract class ContactEvent extends DomainEvent{
	/** @var null|string $_user */
	private $_user;

	/**
	 * ContactEvent constructor.
	 *
	 * @param UUID        $aggregateId identifiant de la prise de contact
	 * @param null|string $user Utilisateur a l'origine de l'événement
	 */
	public function __construct(UUID $aggregateId,?string $user=null) {
		parent::__construct($aggregateId);
		$this->_user = $user;
	}

	/**
	 * @return null|string
	 */
	public function getUser(): ?string {
		return $this->_user;
	}
}