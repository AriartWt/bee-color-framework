<?php
namespace wfw\engine\package\users\domain\events;

use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\UserSettings;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\types\UserType;

/**
 *  Evenement émis lorsqu'un nouvel utilisateur est enregistré
 */
final class UserRegisteredEvent extends UserEvent implements IAggregateRootGeneratedEvent{
	/** @var Login $_login */
	private $_login;
	/** @var Password $_password */
	private $_password;
	/** @var Email $_email */
	private $_email;
	/** @var UserSettings $_settings */
	private $_settings;
	/** @var UserState $_state */
	private $_state;
	/** @var UserType $_type */
	private $_type;
	/** @var array $_args */
	private $_args;
	
	/**
	 * UserRegisteredEvent constructor.
	 *
	 * @param UUID         $id       Identifiant de l'utilisateur
	 * @param Login        $login    Login
	 * @param Password     $password Mot de passe
	 * @param Email        $email    Email
	 * @param UserSettings $settings Paramètres
	 * @param UserState    $state    Etat de l'utilisateur à sa création
	 * @param UserType     $type     Type d'utilisateur.
	 * @param string       $creatorId Identifiant de l'utilisateur ayant créé l'utilisateur courant
	 */
	public function __construct(
		UUID $id,
		Login $login,
		Password $password,
		Email $email,
		UserSettings $settings,
		UserState $state,
		UserType $type,
		string $creatorId
	){
		parent::__construct($id,$creatorId);
		$this->_login = $login;
		$this->_password = $password;
		$this->_email = $email;
		$this->_settings = $settings;
		$this->_state = $state;
		$this->_type  = $type;
		$this->_args = func_get_args();
	}

	/**
	 * @return Login
	 */
	public function getLogin():Login { return $this->_login; }

	/**
	 * @return Password
	 */
	public function getPassword():Password { return $this->_password; }

	/**
	 * @return Email
	 */
	public function getEmail():Email { return $this->_email; }

	/**
	 * @return UserSettings
	 */
	public function getSettings():UserSettings { return $this->_settings; }

	/**
	 * @return UserState
	 */
	public function getState():UserState { return $this->_state; }

	/**
	 * @return UserType
	 */
	public function getType():UserType { return $this->_type; }

	/**
	 * @return array Arguments du constructeur de l'aggrégat
	 */
	public function getConstructorArgs(): array { return $this->_args; }
}