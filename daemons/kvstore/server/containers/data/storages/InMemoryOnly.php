<?php
namespace wfw\daemons\kvstore\server\containers\data\storages;

use wfw\daemons\kvstore\server\containers\data\KVSStorageModeManager;

/**
 *  Stockage en mémoire.
 */
final class InMemoryOnly implements KVSStorageModeManager {
	/** @var array $_data */
	private $_data;

	/**
	 * InMemoryOnlyStorage constructor.
	 */
	public function __construct() {
		$this->_data = [];
	}

	/**
	 *  Obtient la valeur associées à une clé
	 *
	 * @param string $key Clé dont on souhaite obtenir les données
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		if($this->exists($key)){
			return $this->_data[$key];
		}else{
			return null;
		}
	}

	/**
	 *  Enregistre une valeur par une clé
	 *
	 * @param string      $key  Clé de stockage
	 * @param mixed       $data Données associées
	 */
	public function set(string $key, $data) {
		$this->_data[$key]=$data;
	}

	/**
	 *  Supprime une clé et les données associées
	 *
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key) {
		unset($this->_data[$key]);
	}

	/**
	 * @param string $key
	 *
	 * @return bool True si la clé existe, false sinon
	 */
	public function exists(string $key): bool {
		return isset($this->_data[$key]);
	}
}