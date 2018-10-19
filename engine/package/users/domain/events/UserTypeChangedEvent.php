<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 04:14
 */

namespace wfw\engine\package\users\domain\events;


use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\domain\types\UserType;

/**
 *  Change le type d'utilisateur
 */
final class UserTypeChangedEvent extends UserEvent{
	/** @var UserType $_type */
	private $_type;
	
	/**
	 * UsertypeChangedEvent constructor.
	 *
	 * @param UUID     $userId Identifiant de l'utilisateur
	 * @param UserType $type   Nouveau type d'utilisateur
	 * @param string   $modifierId identifiant de l'utilisateur à l'origine de la modification
	 */
	public function __construct(UUID $userId, UserType $type,string $modifierId) {
		parent::__construct($userId,$modifierId);
		$this->_type = $type;
	}

	/**
	 * @return UserType
	 */
	public function getType():UserType{
		return $this->_type;
	}
}