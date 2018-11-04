<?php
namespace wfw\engine\package\users\command;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\states\UserState;

/**
 * Class ChangeUserMail
 * @package wfw\engine\package\users\command
 */
final class ChangeUserMail extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var string $_modifier */
	private $_modifier;
	/** @var bool $_sendmail */
	private $_sendmail;
	/** @var Email $_mail */
	private $_mail;
	/** @var null|UserState $_state */
	private $_state;

	/**
	 * ChangeUserMail constructor.
	 * @param string $userId
	 * @param Email $email Nouvelle adresse mail
	 * @param string $modifier
	 * @param bool $sendMail
	 * @param null|UserState $state
	 */
	public function __construct(
		string $userId,
		Email $email,
		string $modifier,
		bool $sendMail = true,
		?UserState $state = null
	) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_modifier = $modifier;
		$this->_sendmail = $sendMail;
		$this->_mail = $email;
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
	public function getModifier(): string {
		return $this->_modifier;
	}

	/**
	 * @return bool
	 */
	public function sendMail(): bool {
		return $this->_sendmail;
	}

	/**
	 * @return Email
	 */
	public function getMail(): Email {
		return $this->_mail;
	}

	/**
	 * @return null|UserState
	 */
	public function getState(): ?UserState {
		return $this->_state;
	}
}