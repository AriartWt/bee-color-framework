<?php
namespace wfw\engine\package\users\command;

use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\states\UserState;

/**
 * Lance une procédure de récupération de mot de passe.
 */
final class RetrievePassword extends UserCommand{
	/** @var string $_userId */
	private $_userId;
	/** @var null|Password $_password */
	private $_password;
	/** @var null|UserState $_state */
	private $_state;

	/**
	 * RetrievePassword constructor.
	 *
	 * @param string $userId Identifiant de l'utilisateur
	 * @param string $askerId Identifiant du demandeur
	 * @param null|Password $password Nouveau mot de passe. Si non précisé, envoie un mail
	 * @param null|UserState $state
	 */
	public function __construct(
		string $userId,
		string $askerId,
		?Password $password = null,
		?UserState $state=null
	){
		parent::__construct($askerId);
		$this->_userId = $userId;
		$this->_password = $password;
		$this->_state = $state;
	}
	
	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return Password
	 */
	public function getPassword(): ?Password {
		return $this->_password;
	}

	/**
	 * @return null|UserState
	 */
	public function getState(): ?UserState {
		return $this->_state;
	}
}