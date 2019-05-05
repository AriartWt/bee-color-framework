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
	public function get(string $key){
		if($this->contains($key)){
			return apcu_fetch("$this->_namespace::$key");
		}else{
			return null;
		}
	}
	public function set(string $key,$data,float $timeout=0):bool{
		return apcu_add("$this->_namespace::$key",$data,$timeout);
	}
	public function update(string $key,$data,float $timeout=0):bool{
		return apcu_store("$this->_namespace::$key",$data,$timeout);
	}
	public function delete(string $key):bool{
		if($this->contains($key)){
			return apcu_delete("$this->_namespace::$key");
		}else{
			return false;
		}
	}
	public function clear():bool{
		return apcu_clear_cache();
	}
	public function contains(string $key):bool{
		return apcu_exists("$this->_namespace::$key");
	}
	public function current(){
		return parent::current()/*["value"]*/;
	}
}

 