<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/06/18
 * Time: 20:18
 */

namespace wfw\engine\package\users\command;


use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;

/**
 * Permet à un utilisateur de confirmer son inscription
 */
final class ConfirmUserRegistration extends UserCommand {
	/** @var string $_confirmer */
	private $_confirmer;
	/** @var string $_userId */
	private $_userId;
	/** @var UserConfirmationCode $_code */
	private $_code;
	/** @var null|UserState $_state */
	private $_state;

	/**
	 * ConfirmUserRegistration constructor.
	 * @param string $userId Utilisateur concerné
	 * @param UserConfirmationCode $code Code à fournir pour valider la confirmation
	 * @param string $confirmer Utilisateur effectuant la confirmation
	 * @param null|UserState $state Etat de l'utilisateur à la fin de la commande
	 */
	public function __construct(
		string $userId,
		UserConfirmationCode $code,
		string $confirmer = '',
		?UserState $state = null
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
	public function getConfirmer(): string{
		return $this->_confirmer;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string{
		return $this->_userId;
	}

	/**
	 * @return UserConfirmationCode
	 */
	public function getCode(): UserConfirmationCode{
		return $this->_code;
	}

	/**
	 * @return null|UserState
	 */
	public function getState(): ?UserState {
		return $this->_state;
	}
}