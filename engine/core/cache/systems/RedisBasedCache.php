<?php
namespace wfw\engine\core\cache\systems;

use wfw\engine\core\data\DBAccess\NOSQLDB\redis\RedisAccess;
use wfw\engine\core\cache\ICacheSystem;

/**
 *  Systeme de cache utilisant redis. <b style="color:red">Attention : les opération foreach() ne sont pas effectuées de manière atomiques !!</b>
 */
final class RedisBasedCache implements ICacheSystem {
	/**
	 * Namespace des espaces réservés par le cache dans redis
	 */
	protected const CACHE_NAMESPACE="APP::CACHE::";

	/**
	 *  Accés à un serveur redis
	 * @var RedisAccess $_redis
	 */
	private $_redis;

	/**
	 * RedisBasedCache constructor.
	 *
	 * @param RedisAccess $redis Connexion à un serveur redis.
	 */
	public function __construct(RedisAccess $redis) {
		$this->_redis = $redis;
	}

	/**
	 *  Applique le namespace courant sur une clé
	 * @param null|string $key Clé sur laquelle appliquer le namespace
	 *
	 * @return string
	 */
	private function applyNamespace(?string $key=null):string{
		if(is_null($key)){
			return self::CACHE_NAMESPACE;
		}else{
			return self::CACHE_NAMESPACE.$key;
		}
	}

	/**
	 *   Obtient la valeur d'une clé en cache
	 *
	 * @param  string $key Clé de la valeur à rechercher
	 *
	 * @return mixed          Valeur attribuée à la clé
	 */
	public function get(string $key) {
		/** @var $ra \Redis **/
		$ra = $this->_redis;
		return unserialize($ra->get($this->applyNamespace($key)));
	}

	/**
	 *  Cache une variable
	 *
	 * @param string $key     Clé de stockage
	 * @param mixed  $data    Donnée à stocker
	 * @param float  $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 *
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function set(string $key, $data, float $timeout = 0): bool {
		/** @var $ra \Redis **/
		$ra = $this->_redis;
		return $ra->set($this->applyNamespace($key),serialize($data),$timeout);
	}

	/**
	 *   Mets à jour une donnée en cache
	 *
	 * @param  string $key     Clé de la donnée à changer
	 * @param  mixed  $data    Nouvelle données
	 * @param  float  $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 *
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function update(string $key, $data, float $timeout = 0): bool {
		/** @var \Redis $ra **/
		$ra = $this->_redis;
		if($this->contains($key)){
			return $ra->set($this->applyNamespace($key),$data,$timeout);
		}else{
			return false;
		}
	}

	/**
	 *   Supprime une donnée en cache
	 *
	 * @param  string $key Clé de la donnée à supprimer du cache
	 *
	 * @return bool           True si l'opération a réussi, false sinon
	 */
	public function delete(string $key): bool {
		/** @var $ra \Redis **/
		$ra = $this->_redis;
		return $ra->del($this->applyNamespace($key));
	}

	/**
	 *   Vide le cache
	 * @return bool    True si l'opération a réussi, false sinon
	 */
	public function clear(): bool {
		/** @var $ra \Redis **/
		$ra = $this->_redis;
		$ra->eval("
			for i, name in ipairs(redis.call('KEYS', ARGV[1])) do redis.call('DEL', name); end
		",[
			0,
			self::CACHE_NAMESPACE."*"
		]);
		return true;
	}

	/**
	 *   Teste l'existence d'une clé de donnée en cache
	 *
	 * @param  string $key Clé à tester
	 *
	 * @return bool           True si la clé existe, false sinon
	 */
	public function contains(string $key): bool {
		/** @var \Redis $ra */
		$ra = $this->_redis;
		return $ra->exists($this->applyNamespace($key));
	}

	/**
	 *  Curseur
	 * @var int $_cursor
	 */
	private $_cursor;

	/**
	 *  Liste des clés matchant le namespace
	 * @var string[] $_keys
	 */
	private $_keys;

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		return $this->get($this->_keys[$this->_cursor]);
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		$this->_cursor++;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return $this->_keys[$this->_cursor];
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		return $this->_cursor<count($this->_keys);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		/** @var \Redis $ra */
		$ra = $this->_redis;
		$this->_keys = $ra->keys($this->applyNamespace()."*");
		$this->_cursor = 0;
	}
}