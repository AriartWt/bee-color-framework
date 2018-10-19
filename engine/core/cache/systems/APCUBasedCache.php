<?php 
namespace wfw\engine\core\cache\systems;

use wfw\engine\core\cache\errors\CacheSystemIncompatibility;
use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\cache\wfw;

/**
 *  Gestionnaire de cache utilisant l'extension php APCU
 */
final class APCUBasedCache implements ICacheSystem{
	/**
	 *  Constructeur
	 * @throws CacheSystemCompatibilityException si les fonction apcu_* ne sont aps disponibles.
	 */
	public function __construct(){
		if(!function_exists("apcu_fetch")){
			throw new CacheSystemIncompatibility("Cannot create ".static::class." : apcu's php functions are not availables. Maybe apcu's extension isn't installed on that server !");
		}
	}
	public function get(string $key){
		if($this->contains($key)){
			return apcu_fetch($key);
		}else{
			return null;
		}
	}
	public function set(string $key,$data,float $timeout=0):bool{
		return apcu_add($key,$data,$timeout);
	}
	public function update(string $key,$data,float $timeout=0):bool{
		return apcu_store($key,$data,$timeout);
	}
	public function delete(string $key):bool{
		if($this->contains($key)){
			return apcu_delete($key);
		}else{
			return false;
		}
	}
	public function clear():bool{
		return apcu_clear_cache();
	}
	public function contains(string $key):bool{
		return apcu_exists($key);
	}
	public function current(){
		return parent::current()/*["value"]*/;
	}
}

 