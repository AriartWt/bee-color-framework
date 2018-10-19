<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/01/18
 * Time: 08:13
 */

namespace wfw\daemons\kvstore\server\containers\params\worker;

use wfw\daemons\kvstore\server\environment\IKVSContainer;

/**
 *  Paramètres attendus par un worker.
 */
final class ContainerWorkerParams
{
	/**
	 * @var IKVSContainer $_container
	 */
	private $_container;

	/**
	 * @var string $_serverKey
	 */
	private $_serverKey;

	/**
	 * @var string $_dbPath
	 */
	private $_dbPath;

	/**
	 * @var string $_socketDir
	 */
	private $_socketDir;

	/**
	 * ContainerWorkerParams constructor.
	 *
	 * @param IKVSContainer $container Container à gérer
	 * @param string                $serverKey Clé du serveur KVS (si la clé n'est pas bonne, les requêtes sont ignorées)
	 * @param string                $dbPath    Chemin d'accés au dossier de la base de donnée KVS
	 * @param string                $socketDir Chemin vers le dossier devant contenir la socket de communication unix
	 */
	public function __construct(IKVSContainer $container,string $serverKey,string $dbPath, string $socketDir="/tmp")
	{
		$this->_container = $container;
		$this->_serverKey = $serverKey;
		if(file_exists($dbPath)){
			$this->_dbPath = $dbPath;
		}else{
			throw new \InvalidArgumentException("$dbPath is not a valid directory !");
		}
		if(file_exists($socketDir)){
			$this->_socketDir = $socketDir;
		}else{
			throw new \InvalidArgumentException("$socketDir is not a valid directory !");
		}
	}

	/**
	 * @return string Repertoire d'écriture de la socket de communication du worker.
	 */
	public function getSocketDir():string{
		return $this->_socketDir;
	}

	/**
	 * @return IKVSContainer Container géré par le worker
	 */
	public function getContainer():IKVSContainer{
		return $this->_container;
	}

	/**
	 *  Vérifie que la clé entrée correspond à la clé serveur
	 *
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 */
	public function matchServerKey(string $key):bool{
		return $this->_serverKey === $key;
	}

	/**
	 * @return string Chemind d'accés au dossier de la base de données KVS
	 */
	public function getDbPath():string{
		return $this->_dbPath;
	}
}