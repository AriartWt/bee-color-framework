<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/06/18
 * Time: 16:25
 */

namespace wfw\engine\package\users\command;

use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\settings\UserSettings;
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\types\UserType;

/**
 * Enregistre un nouvel utilisateur
 */
final class RegisterUser extends UserCommand{
	/** @var Login $_login */
	private $_login;
	/** @var Password $_password */
	private $_password;
	/** @var Email $_email */
	private $_email;
	/** @var string $_creator */
	private $_creator;
	/** @var UserType $_type */
	private $_type;
	/** @var UserState $_state */
	private $_state;
	/** @var UserSettings $_settings */
	private $_settings;
	/** @var bool $_sendMail */
	private $_sendMail;

	/**
	 * RegisterUser constructor.
	 *
	 * @param Login $login Login de l'utilisateur
	 * @param Password $password Mot de passe de l'utilisateur
	 * @param Email $email Email de l'utilisateur
	 * @param UserType $type Type d'utilisateur
	 * @param string $creatorId Créateur de l'utilisateur
	 * @param null|UserSettings $settings Paramètres de l'utilisateur
	 * @param null|UserState $state Etat de l'utilisateur à sa création
	 * @param bool $sendMail Si true et state a WaitingForRegisteringConfirmation, envoie le mail
	 */
	public function __construct(
		Login $login,
		Password $password,
		Email $email,
		UserType $type,
		string $creatorId,
		?UserSettings $settings = null,
		?UserState $state = null,
		bool $sendMail = true
	){
		parent::__construct();
		$this->_login = $login;
		$this->_password = $password;
		$this->_email = $email;
		$this->_type = $type;
		$this->_creator = $creatorId;
		$this->_state = $state ?? new EnabledUser();
		$this->_settings = $settings ?? new InMemoryUserSettings();
		$this->_sendMail = $sendMail;
	}
	
	/**
	 * @return Login
	 */
	public function getLogin(): Login {
		return $this->_login;
	}
	
	/**
	 * @return Password
	 */
	public function getPassword(): Password {
		return $this->_password;
	}
	
	/**
	 * @return Email
	 */
	public function getEmail(): Email {
		return $this->_email;
	}
	
	/**
	 * @return string
	 */
	public function getCreator(): string {
		return $this->_creator;
	}
	
	/**
	 * @return UserType
	 */
	public function getType(): UserType {
		return $this->_type;
	}
	
	/**
	 * @return UserState
	 */
	public function getState():UserState {
		return $this->_state;
	}
	
	/**
	 * @return UserSettings
	 */
	public function getSettings(): UserSettings {
		return $this->_settings;
	}

	/**
	 * @return bool
	 */
	public function sendMail():bool{
		return $this->_sendMail;
	}
}