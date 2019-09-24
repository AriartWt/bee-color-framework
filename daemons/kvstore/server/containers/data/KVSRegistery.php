<?php
namespace wfw\daemons\kvstore\server\containers\data;

use wfw\engine\lib\PHP\types\Type;

/**
 *  Registre de clés
 */
final class KVSRegistery implements IKVSRegistery {
	/** @var IKVSRegisteryKey[] $_keys */
	private $_keys;

	/**
	 * KVSRegistry constructor.
	 *
	 * @param IKVSRegisteryKey[] $keys (optionnel) Clés
	 */
	public function __construct(array $keys=[]) {
		$this->_keys = $keys;
		foreach($keys as $k=>$key){
			if(!($key instanceof IKVSRegisteryKey)){
				throw new \InvalidArgumentException("Invalid key at offset $k : all key must implements ".IKVSRegisteryKey::class);
			}
		}
	}

	/**
	 *  Ajoute une clé au registre
	 *
	 * @param IKVSRegisteryKey $key Clé à ajouter
	 */
	public function add(IKVSRegisteryKey $key) {
		$this->_keys[$key->getName()] = $key;
	}

	/**
	 * @param string $name Nom de la clé à obtenir
	 *
	 * @return null|IKVSRegisteryKey Clé demandée
	 */
	public function get(string $name): ?IKVSRegisteryKey {
		if($this->exists($name)){
			return $this->_keys[$name];
		}else{
			return null;
		}
	}

	/**
	 * @param string $name Clé à supprimer
	 */
	public function remove(string $name) {
		if($this->exists($name)){
			unset($this->_keys[$name]);
		}
	}

	/**
	 * @param string $name Nom de la clé
	 *
	 * @return bool True si la clé existe dans le registre, false sinon
	 */
	public function exists(string $name): bool {
		return isset($this->_keys[$name]);
	}

	/** @var int $_cursor */
	private $_cursor;
	/** @var $_tmpKeys */
	private $_tmpKeys;

	/**
	 * Moves the cursor to the first (key,element) pair.
	 * @return void
	 */
	function rewind() {
		$this->_cursor = 0;
		$this->_tmpKeys = $this->_keys;
	}

	/**
	 * Check if the cursor is on a valid element.
	 * @return bool
	 */
	function valid() {
		return $this->_cursor < count($this->_tmpKeys);
	}

	/**
	 * Returns the key of the current (key,element) pair under the cursor.
	 * Prerequisite: the cursor must be over a valid element, or the behaviour
	 * is unspecified; implementations may throw an unchecked exception to
	 * help debugging programs.
	 * @return mixed
	 */
	function key() {
		return array_keys($this->_tmpKeys)[$this->_cursor];
	}

	/**
	 * Returns the element of the current (key,element) pair under the cursor.
	 * Prerequisite: the cursor must be over a valid element, or the behaviour
	 * is unspecified; implementations may throw an unchecked exception to
	 * help debugging programs.
	 * @return mixed
	 */
	function current() {
		return array_values($this->_tmpKeys)[$this->_cursor];
	}

	/**
	 * Move the cursor to the next (key,element) pair, if any. If the cursor
	 * is already beyond the last pair, does nothing.
	 * @return void
	 */
	function next() {
		$this->_cursor++;
	}

	/**
	 * @param string $offset Nom de la clé
	 * @return bool
	 */
	function offsetExists($offset) {
		return $this->exists($offset);
	}

	/**
	 * @param string $offset Clé à obtenir
	 *
	 * @return IKVSRegisteryKey
	 */
	function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * @param string                   $offset Nom de la clé
	 * @param IKVSRegisteryKey $value  Clé
	 */
	function offsetSet($offset,$value) {
		if(is_string($offset)){
			if($value instanceof IKVSRegisteryKey){
				if($offset === $value->getName()){
					$this->add($value);
				}else{
					throw new \InvalidArgumentException("Given keyx mismatch !");
				}
			}else{
				throw new \InvalidArgumentException("Value have to be an instance of ".IKVSRegisteryKey::class." , ".(new Type($value))->get()." given !");
			}
		}else{
			throw new \InvalidArgumentException("Only string keys are allowed !");
		}
	}

	/**
	 * @param string $offset Clé à supprimer
	 */
	function offsetUnset($offset) {
		$this->remove($offset);
	}

	/**
	 * @return int Nombre de clés dans le registre
	 */
	public function getLength(): int {
		return count($this->_keys);
	}
}