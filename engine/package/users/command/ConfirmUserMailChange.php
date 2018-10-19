<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:45
 */

namespace wfw\engine\package\users\command;

use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Commande de validation de changement d'adresse mail
 */
final class ConfirmUserMailChange extends UserCommand {
	/** @var string $_userId */
	private $_userId;
	/** @var string $_confirmer */
	private $_confirmer;
	/** @var UserConfirmationCode $_code */
	private $_code;
	/** @var null|UserState $_state */
	private $_state;

	/**
	 * ConfirmUserMailChange constructor.
	 * @param string $userId
	 * @param UserConfirmationCode $code
	 * @param string $confirmer
	 * @param null|UserState $state Etat de l'utilisateur à la fin du changement de mail
	 */
	public function __construct(
		string $userId,
		UserConfirmationCode $code,
		string $confirmer,
		?UserState $state=null
	){
		parent::__construct();
		$this->_userId = $userId;
		$this->_confirmer = $confirmer;
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
	public function getConfirmer(): string {
		return $this->_confirmer;
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