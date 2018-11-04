<?php
namespace wfw\engine\package\users\domain\events;

use wfw\engine\core\domain\events\DomainEvent;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Evenement concernant l'aggregate root User
 */
abstract class UserEvent extends DomainEvent{
	/** @var string $_modifierId */
	private $_modifierId;
	
	/**
	 * UserEvent constructor.
	 *
	 * @param UUID   $aggregateId Identifiant de l'aggregat
	 * @param string $modifierId  Identifiant de l'utilisateur à l'origine de la modification.
	 */
	public function __construct(UUID $aggregateId,string $modifierId) {
		parent::__construct($aggregateId);
		$this->_modifierId = $modifierId;
	}
	
	/**
	 * @return string
	 */
	public function getModifier():string{
		return $this->_modifierId;
	}
}