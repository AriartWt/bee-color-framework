<?php
namespace wfw\engine\package\users\command;

use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Remet à 0 un mot de passe.
 */
final class ResetPassword extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var string $_askerId */
	private $_askerId;
	/** @var Password $_password */
	private $_password;
	/** @var UserConfirmationCode $_code */
	private $_code;
	/** @var null|UserState $_state */
	private $_state;

	/**
	 * ResetPassword constructor.
	 * @param string $userId
	 * @param string $askerId
	 * @param Password $password
	 * @param UserConfirmationCode $code
	 * @param null|UserState $state
	 */
	public function __construct(
		string $userId,
		string $askerId,
		Password $password,
		UserConfirmationCode $code,
		?UserState $state = null
	) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_askerId = $askerId;
		$this->_password = $password;
		$this->_code = $code;
		$this->_state = $state;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return string
	 */
	public function getAskerId(): string {
		return $this->_askerId;
	}

	/**
	 * @return Password
	 */
	public function getPassword(): Password {
		return $this->_password;
	}

	/**
	 * @return UserConfirmationCode
	 */
	public function getCode(): UserConfirmationCode {
		return $this->_code;
	}

	/**
	 * @return null|UserState
	 */
	public function getState(): ?UserState {
		return $this->_state;
	}
}