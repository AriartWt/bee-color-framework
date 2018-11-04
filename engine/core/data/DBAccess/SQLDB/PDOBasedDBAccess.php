<?php
namespace wfw\engine\core\data\DBAccess\SQLDB;

use InvalidArgumentException;
use PDO;
use wfw\engine\core\data\query\CompiledQuery;
use wfw\engine\core\data\query\ISQLQuery;

/**
 *  Accés à une base de données en utilisant PDO
 */
abstract class PDOBasedDBAccess implements IDBAccess {
	/** @var string $_db */
	private $_db;
	/** @var string $_host */
	private $_host;
	/** @var string $_dbName */
	private $_dbName;
	/** @var string $_login */
	private $_login;
	/** @var string $_password */
	private $_password;

	/**
	 *  Connexion à la base de données
	 * @var PDO $_connection
	 */
	protected $_connection;

	/**
	 *  PDOBasedDBAccess constructor.
	 *
	 * @param string $db       Nom du DBMS (exemple : "mysql" )
	 * @param string $host     Hôte du DBMS
	 * @param string $dbName   Nom de la base de données
	 * @param string $login    Login
	 * @param string $password Mot de passe
	 */
	public function __construct(
		string $db,
		string $host,
		string $dbName,
		string $login,
		string $password
	) {
		$this->_db = $db;
		$this->_host = $host;
		$this->_login = $login;
		$this->_dbName = $dbName;
		$this->_password = $password;
		$this->connect();
	}

	private function connect():void{
		$this->_connection = new PDO("$this->_db:host=$this->_host;dbname=$this->_dbName;",$this->_login,$this->_password, array(PDO::MYSQL_ATTR_INIT_COMMAND=> 'SET NAMES utf8'));
		$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 *  Retourne la valeur de paramètre PDO correspondant à la donnée passée en paramètre.
	 * @param mixed $data Donnée
	 * @return int
	 */
	protected function getPDOType($data){
		if(is_bool($data)){
			return PDO::PARAM_BOOL;
		}else if(is_float($data)){
			return PDO::PARAM_STR;
		}else if(is_int($data)){
			return PDO::PARAM_INT;
		}else if(is_null($data)){
			return PDO::PARAM_NULL;
		}else if(is_string($data)){
			return PDO::PARAM_STR;
		}else throw new InvalidArgumentException(
			"No corresponding PDO::PARAM_* constant value for given value ! (".gettype($data).")"
		);
	}

	/**
	 *  Execute une requête SQL et retourne le résultat
	 *
	 * @param ISQLQuery $query Requête à executer
	 *
	 * @return \PDOStatement
	 * @throws \Exception
	 */
	public function execute(ISQLQuery $query) : \PDOStatement {
		$query = $query->compile();
		try{
			$pre = $this->prepare($query);
			$pre->execute();
			return $pre;
		}catch(\PDOException $e){
			if(preg_match("/^(.*2006 MySQL server has gone away.*)$/",$e->getMessage())){
				$this->connect();
				$pre = $this->prepare($query);
				$pre->execute();
				return $pre;
			}else throw $e;
		}catch(\ErrorException $e){
			if(preg_match("/^(.*PDOStatement::execute\(\):.*Broken pipe.*)$/",$e->getMessage())){
				$this->connect();
				$pre = $this->prepare($query);
				$pre->execute();
				return $pre;
			}else throw $e;
		}
	}

	/**
	 * @param CompiledQuery $query
	 * @return \PDOStatement
	 * @throws InvalidArgumentException
	 */
	private function prepare(CompiledQuery $query):\PDOStatement{
		$pre=$this->_connection->prepare($query->getQueryString());
		foreach($query->getParams() as $k=>$v){
			if(!$pre->bindValue((is_int($k))?$k+1:$k,$v,$this->getPDOType($v))){
				throw new \Exception("Cannot bind : $k : $v");
			}
		}
		return $pre;
	}

	public function beginTransaction() {
		$this->_connection->beginTransaction();
	}

	public function commit() {
		$this->_connection->commit();
	}

	public function rollBack() {
		$this->_connection->rollBack();
	}

	/**
	 *  True si l'access est en cours de transaction, false sinon
	 * @return bool
	 */
	public function inTransaction(): bool {
		return $this->_connection->inTransaction();
	}
}