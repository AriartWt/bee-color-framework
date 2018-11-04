<?php
namespace wfw\engine\package\users\data\model\DTO;

use wfw\engine\core\data\model\DTO\DTO;
use wfw\engine\lib\PHP\objects\PHPClassName;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\UserSettings;
use wfw\engine\package\users\domain\states\UserState;
use wfw\engine\package\users\domain\types\UserType;

/**
 *  DTO pour la classe User
 */
class User extends DTO {
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
	/** @var string $_creatorId */
	private $_creatorId;

	/**
	 * User constructor.
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
		$this->_creatorId = $creatorId;
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
	 * @return UserType
	 */
	public function getType(): UserType {
		return $this->_type;
	}

	/**
	 * @return string
	 */
	public function getCreator(): string {
		return $this->_creatorId;
	}

	/**
	 * @return array
	 */
	public function skipProperties(): array {
		return array_merge(parent::skipProperties(),[
			"_password"
		]);
	}

	/**
	 * @return array
	 */
	public function transformProperties(): array {
		$type = new PHPClassName(get_class($this->_type));
		$state = new PHPClassName(get_class($this->_state));
		return array_merge(parent::transformProperties(),[
			"_login" => (string) $this->_login,
			"_settings" => $this->_settings->getArray(''),
			"_email" => (string) $this->_email,
			"_type" => $type->getName(),
			"_state" => $state->getName()
		]);
	}
}