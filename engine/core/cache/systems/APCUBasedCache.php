<?php 
namespace wfw\engine\core\cache\systems;

use wfw\engine\core\cache\errors\CacheSystemIncompatibility;
use wfw\engine\core\cache\ICacheSystem;

/**
 *  Gestionnaire de cache utilisant l'extension php APCU
 */
final class APCUBasedCache implements ICacheSystem{
	/** @var null|string $_namespace */
	private $_namespace;

	/**
	 *  Constructeur
	 *
	 * @param null|string $namespace Prefix added before all key to avoid collision between multi
	 *                               project environment
	 * @throws CacheSystemIncompatibility
	 */
	public function __construct(?string $namespace=null){
		if(!function_exists("apcu_fetch")){
			throw new CacheSystemIncompatibility(
				"Cannot create ".static::class." : apcu's php functions are not availables. "
				."Maybe apcu's extension isn't installed on that server !"
			);
		}
		$this->_namespace = $namespace ?? '';
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function get(string $key){
		if($this->contains($key)){
			return apcu_fetch("$this->_namespace::$key");
		}else{
			return null;
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param float  $timeout
	 * @return bool
	 */
	public function set(string $key, $data, float $timeout=0):bool{
		return apcu_add("$this->_namespace::$key",$data,$timeout);
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param float  $timeout
	 * @return bool
	 */
	public function update(string $key, $data, float $timeout=0):bool{
		return apcu_store("$this->_namespace::$key",$data,$timeout);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete(string $key):bool{
		if($this->contains($key)){
			return apcu_delete("$this->_namespace::$key");
		}else{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function clear():bool{
		return apcu_clear_cache();
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function contains(string $key):bool{
		return apcu_exists("$this->_namespace::$key");
	}

	/**
	 * [WARNING] For now, passing an array of PCRE regular expression will never return any result.
	 * As a workaround, an array of length 1 will be chaned in a simple string before being passed
	 * to APCUIterator::__construct()
	 * @see https://bugs.php.net/bug.php?id=78002
	 * @param string[] $keys Clé des valeurs à chercher
	 * @return \Traversable
	 */
	public function getAll(array $keys): \Traversable {
		foreach($keys as $k=>$key){
			$keys[$k]="/".str_replace("/",'\/',"^$this->_namespace::$key")."/";
		}
		if(count($keys) === 1) $keys = array_pop($keys);
		return new \APCUIterator($keys);
	}

	/**
	 * @param string[] $keys Clés des valeurs à supprimer du cache
	 */
	public function deleteAll(array $keys) {
		apcu_delete($this->getAll($keys));
	}
}

 