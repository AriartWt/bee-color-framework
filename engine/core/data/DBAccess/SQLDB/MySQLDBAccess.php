<?php
namespace wfw\engine\core\data\DBAccess\SQLDB;


/**
 *  Accés à une base de données MYSQL utilisant l'objet PDO
 */
class MySQLDBAccess extends PDOBasedDBAccess {
	/**
	 *  MySQLDBAccess constructor.
	 *
	 * @param string $host     Hôte de la base de données
	 * @param string $dbName   Nom de la base de données
	 * @param string $login    Login
	 * @param string $password Mot de passe
	 */
	public function __construct(string $host, string $dbName, string $login, string $password) {
		parent::__construct("mysql", $host, $dbName, $login, $password);
	}
}