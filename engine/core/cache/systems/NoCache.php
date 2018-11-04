<?php 
namespace wfw\engine\core\cache\systems;
use wfw\engine\core\cache\ICacheSystem;

/**
 *  NoCache permet de substituer le systeme de cache courant par un systeme de cache inactif. Utilse notament pour désactiver le cache via les configurations de l'application. NoCache est chargé par défaut par wfw::lib::cache::Cache
 */
class NoCache implements ICacheSystem{
	public function __construct() {}

	public function get(string $key){
		return null;
	}
	public function set(string $key,$data,float $timeout=0):bool{
		return false;
	}
	public function update(string $key,$data,float $timeout=0):bool{
		return false;
	}
	public function delete(string $key):bool{
		return false;
	}
	public function clear():bool{
		return false;
	}
	public function contains(string $key):bool{
		return false;
	}

	public function rewind(){}
	public function key(){
		return 0;
	}
	public function current(){
		return null;
	}
	public function valid(){
		return false;
	}
	public function next(){}
}
 