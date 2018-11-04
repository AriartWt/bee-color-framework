<?php
namespace wfw\engine\package\users\command;

use wfw\engine\package\users\domain\types\UserType;

/**
 * Change le type d'un utilisateur
 */
final class ChangeType extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var UserType $_type */
	private $_type;
	/** @var string $_modifierId */
	private $_modifierId;

	/**
	 * ChangeType constructor.
	 * @param string $userId
	 * @param UserType $type
	 * @param string $modifierId
	 */
	public function __construct(string $userId, UserType $type, string $modifierId) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_type = $type;
		$this->_modifierId = $modifierId;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return UserType
	 */
	public function getType(): UserType {
		return $this->_type;
	}

	/**
	 * @return string
	 */
	public function getModifierId(): string {
		return $this->_modifierId;
	}
}