<?php
namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Utilisateur du MSServer
 */
final class MSServerUser implements IMSServerUser {
    /** @var string $_name */
	private $_name;
	/** @var string $_password */
	private $_password;

	/**
	 * MSServerUser constructor.
	 *
	 * @param string $name     Nom de l'utilisateur
	 * @param string $password Mot de passe de l'utilisateur
	 */
	public function __construct(string $name,string $password) {
		$this->_password = $password;
		$this->_name = $name;
	}

	/**
	 * @return string Nom de l'utilisateur
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 *  Teste la validité d'un mot de passe.
	 *
	 * @param string $password Mot de passe à tester
	 *
	 * @return bool
	 */
	public function matchPassword(string $password): bool {
		return $this->_password === $password;
	}
}