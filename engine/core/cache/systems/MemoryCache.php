<?php

namespace wfw\engine\core\cache\systems;

use wfw\engine\core\cache\ICacheSystem;

/**
 * Class MemoryCache
 *
 * @package wfw\engine\core\cache\systems
 */
final class MemoryCache implements ICacheSystem {
	/** @var array $_data */
	private $_data;
	/** @var null|string $_namespace */
	private $_namespace;

	/**
	 * MemoryCache constructor.
	 *
	 * @param null|string $namespace
	 */
	public function __construct(?string $namespace=null) {
		$this->_data = [];
		$this->_namespace = $namespace;
	}

	/**
	 *   Obtient la valeur d'une clé en cache
	 *
	 * @param  string $key Clé de la valeur à rechercher
	 * @return mixed          Valeur attribuée à la clé
	 */
	public function get(string $key) {
		return $this->_data["$this->_namespace::$key"]["data"] ?? null;
	}

	/**
	 * @param string[] $keys Clé des valeurs à chercher
	 * @return iterable
	 */
	public function getAll(array $keys): iterable {
		return array_intersect_key(
			$this->_data,
			array_flip(array_map(function($key){
				return "$this->_namespace::$key";
			},$keys)));
	}

	/**
	 * @param string[] $keys Clés des valeurs à supprimer du cache
	 */
	public function deleteAll(array $keys) {
		foreach($keys as $key) if($this->contains($key))
			unset($this->_data["$this->_namespace::$key"]);
	}

	/**
	 *  Cache une variable
	 *
	 * @param string $key     Clé de stockage
	 * @param mixed  $data    Donnée à stocker
	 * @param float  $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function set(string $key, $data, float $timeout = 0): bool {
		$this->_data["$this->_namespace::$key"] = [
			"data" => $data,
			"timeout" => microtime(true) + $timeout
		];
		return true;
	}

	/**
	 *   Mets à jour une donnée en cache
	 *
	 * @param  string $key     Clé de la donnée à changer
	 * @param  mixed  $data    Nouvelle données
	 * @param  float  $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function update(string $key, $data, float $timeout = 0): bool {
		if($this->contains($key)) return $this->set($key,$data,$timeout);
		else return false;
	}

	/**
	 *   Supprime une donnée en cache
	 *
	 * @param  string $key Clé de la donnée à supprimer du cache
	 * @return bool           True si l'opération a réussi, false sinon
	 */
	public function delete(string $key): bool {
		if($this->contains($key)){
			unset($this->_data["$this->_namespace::$key"]);
			return true;
		}else return false;
	}

	/**
	 *   Vide le cache
	 *
	 * @return bool    True si l'opération a réussi, false sinon
	 */
	public function clear(): bool {
		$this->_data = [];
		return true;
	}

	/**
	 *   Teste l'existence d'une clé de donnée en cache
	 *
	 * @param  string $key Clé à tester
	 * @return bool           True si la clé existe, false sinon
	 */
	public function contains(string $key): bool {
		if(isset($this->_data["$this->_namespace::$key"])){
			if(microtime(true) <= $this->_data["$this->_namespace::$key"]["timeout"]) return true;
			else unset($this->_data["$this->_namespace::$key"]);
		}
		return false;
	}
}