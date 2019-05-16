<?php 
namespace wfw\engine\core\cache\systems;
use wfw\engine\core\cache\ICacheSystem;

/**
 *  NoCache permet de substituer le systeme de cache courant par un systeme de cache inactif.
 *  Utilsé notament pour désactiver le cache via les configurations de l'application.
 */
class NoCache implements ICacheSystem{
	public function __construct() {}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function get(string $key){
		return null;
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param float  $timeout
	 * @return bool
	 */
	public function set(string $key, $data, float $timeout=0):bool{
		return false;
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param float  $timeout
	 * @return bool
	 */
	public function update(string $key, $data, float $timeout=0):bool{
		return false;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete(string $key):bool{
		return false;
	}

	/**
	 * @return bool
	 */
	public function clear():bool{
		return false;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function contains(string $key):bool{
		return false;
	}

	/**
	 * @param string[] $keys Clé des valeurs à chercher
	 * @return \Traversable
	 */
	public function getAll(array $keys): \Traversable {}

	/**
	 * @param string[] $keys Clés des valeurs à supprimer du cache
	 */
	public function deleteAll(array $keys) {}
}
 