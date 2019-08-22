<?php

namespace wfw\daemons\rts\server\local;

/**
 * Class RTSLogin
 *
 * @package wfw\daemons\rts\server\local
 */
final class RTSLogin implements IRTSLocalCommand {
	/** @var string $_login */
	private $_login;
	/** @var string $_password */
	private $_password;

	/**
	 * RTSLogin constructor.
	 *
	 * @param string $login
	 * @param string $password
	 */
	public function __construct(string $login, string $password) {
		$this->_login = $login;
		$this->_password = $password;
	}

	/**
	 * @return string
	 */
	public function getLogin(): string {
		return $this->_login;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->_password;
	}


	/**
	 * String representation of object
	 *
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize([$this->_login,$this->_password]);
	}

	/**
	 * Constructs the object
	 *
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) {
		list(
			$this->_login,
			$this->_password
		) = unserialize($serialized);
	}
}