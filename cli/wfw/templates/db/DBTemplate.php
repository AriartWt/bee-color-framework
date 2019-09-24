<?php
namespace wfw\cli\wfw\templates\db;

/**
 * Crée un fichier SQL regroupant les commandes à effectuer pour créer la base de données
 * d'un nouveau projet ainsi qu'un nouvel utilisateur
 */
final class DBTemplate {
	/** @var string $_name */
	private $_name;
	/** @var string $_user */
	private $_user;
	/** @var string $_password */
	private $_password;
	/** @var string $_filePath */
	private $_filePath;
	/** @var string $_rootUser */
	private $_rootUser;
	/** @var string $_rootPassword */
	private $_rootPassword;

	/**
	 * DBTemplate constructor.
	 *
	 * @param string $name     Nom de la base de données
	 * @param string $user     Nom de l'utilisateur
	 * @param string $password Password de l'utilisateur
	 * @param string $rootUser
	 * @param string $rootPassword
	 * @param string $path     (optionnel) chemin d'accès au template
	 */
	public function __construct(
		string $name,
		string $user,
		string $password,
		string $rootUser,
		string $rootPassword,
		string $path=__DIR__."/db.template.php"
	){
		$this->_name = $name;
		$this->_user = $user;
		$this->_password = $password;
		$this->_filePath = $path;
		$this->_rootPassword = $rootPassword;
		$this->_rootUser = $rootUser;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		ob_start();
		require $this->_filePath;
		return ob_get_clean();
	}
}