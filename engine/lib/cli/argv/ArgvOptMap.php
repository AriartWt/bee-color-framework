<?php
namespace wfw\engine\lib\cli\argv;

/**
 *  Map des ArgvOpt par nom
 */
class ArgvOptMap implements \Iterator {
	private $_array=[];
	/**
	 * ArgvOptMap constructor. Ajoute automatiquement la commande --help à la fin des options pour l'affichage de l'aide sur la commande.
	 *
	 * @param ArgvOpt[] $opts Liste d'options
	 */
	public function __construct(array $opts) {
		$opts[]=new ArgvOpt("--help","Affiche l'aide sur l'utilisation de la commande.",0,null,true);
		foreach($opts as $opt){
			$this->_array[$opt->getName()]=$opt;
		}
	}

	/**
	 * @return int
	 */
	public function getLength():int{
		return count($this->_array);
	}

	/**
	 * @param string $key
	 *
	 * @return ArgvOpt
	 */
	public function get(string $key):ArgvOpt{
		if(isset($this->_array[$key])){
			return $this->_array[$key];
		}else{
			throw new \InvalidArgumentException("$key not found !");
		}
	}

	/**
	 *  Retourne true si l'option de nom $key existe
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 */
	public function exists(string $key):bool{
		return isset($this->_array[$key]);
	}

	private $_cursor;

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		return array_values($this->_array)[$this->_cursor];
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
		return array_keys($this->_array)[$this->_cursor];
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
		return $this->_cursor < count($this->_array);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		$this->_cursor = 0;
	}
}