<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 12/12/17
 * Time: 04:54
 */

namespace wfw\engine\package\users\data\model\objects;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\core\data\model\ModelObject;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\UserSettings;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\types\UserType;

/**
 * Class User
 *
 * @package wfw\engine\package\users\data\model\objects
 */
class User extends ModelObject
{
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
	/** @var string $_creator */
	private $_creator;

	/**
	 *  User constructor.
	 *
	 * @param UUID $id
	 * @param Login $login
	 * @param Password $password
	 * @param Email $email
	 * @param UserSettings $settings
	 * @param UserState $state
	 * @param UserType $type
	 * @param string $creatorId
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
		parent::__construct($id);
		$this->_login = $login;
		$this->_password = $password;
		$this->_email = $email;
		$this->_settings = $settings;
		$this->_state = $state;
		$this->_type  = $type;
		$this->_creator = $creatorId;
	}

	/**
	 * @return string
	 */
	public function getCreator(): string {
		return $this->_creator;
	}

	/**
	 * @return Login
	 */
	public function getLogin(): Login {
		return $this->_login;
	}

	/**
	 * @param Login $login
	 */
	public function setLogin(Login $login): void {
		$this->_login = $login;
	}

	/**
	 * @return Password
	 */
	public function getPassword(): Password {
		return $this->_password;
	}

	/**
	 * @param Password $password
	 */
	public function setPassword(Password $password): void {
		$this->_password = $password;
	}

	/**
	 * @return Email
	 */
	public function getEmail(): Email {
		return $this->_email;
	}

	/**
	 * @param Email $email
	 */
	public function setEmail(Email $email): void {
		$this->_email = $email;
	}

	/**
	 * @return UserSettings
	 */
	public function getSettings(): UserSettings {
		return $this->_settings;
	}

	/**
	 * @return UserState
	 */
	public function getState(): UserState {
		return $this->_state;
	}

	/**
	 * @param UserState $state
	 */
	public function setState(UserState $state): void {
		$this->_state = $state;
	}

	/**
	 * @return UserType
	 */
	public function getType(): UserType {
		return $this->_type;
	}

	/**
	 * @param UserType $type
	 */
	public function setType(UserType $type): void {
		$this->_type = $type;
	}

	/**
	 *  Transforme l'objet courant en DTO pour garder la cohérence du Model
	 * @return IDTO
	 */
	public function toDTO(): IDTO {
		return new \wfw\engine\package\users\data\model\DTO\User(
			$this->getId(),
			$this->_login,
			$this->_password,
			$this->_email,
			unserialize(serialize($this->_settings)),
			$this->_state,
			$this->_type,
			$this->_creator
		);
	}
}