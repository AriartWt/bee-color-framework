<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/02/18
 * Time: 06:22
 */

namespace wfw\engine\package\miel\model;

use wfw\engine\lib\data\string\serializer\ISerializer;

/**
 * Model de gestion clé/valeurs pour le système MIEL.
 */
abstract class MielModel implements IMielModel {
	/** @var bool $_loadedFromFile */
	private $_loadedFromFile;
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var string $_dbFile */
	private $_dbFile;
	/** @var array $_data */
	private $_data;
	/** @var int $_cursor */
	private $_cursor;
	/** @var bool $_modified */
	private $_modified;

	/**
	 * HoneyPot constructor.
	 *
	 * @param ISerializer $serializer Permet de serialiser les donnéesà stocker dans le fichier
	 * @param string      $dbFile Fichier de stockage des clés/params/valeurs
	 */
	public function __construct(ISerializer $serializer,string $dbFile) {
		$this->_serializer = $serializer;
		if(!is_dir(dirname($dbFile))){
			throw new \InvalidArgumentException("$dbFile is not under a valid directory !");
		}
		$this->_dbFile = $dbFile;
		if(file_exists($dbFile)){
			$this->_data = $this->_serializer->unserialize(file_get_contents($dbFile));
			$this->_loadedFromFile = true;
		}else{
			$this->_data = [];
			$this->_loadedFromFile = false;
		}
		$this->_modified = false;
	}

	/**
	 * @param array $data Tableau sous la forme "clé"=>["valeur" => mixed, "params" => []]
	 * @throws \InvalidArgumentException
	 */
	protected final function setArray(array $data){
		foreach($data as $k=>$v){
			if(
				is_array($v)
				&& isset($v["value"])
			){
				$this->set($k,$v["value"]);
				if(isset($v["params"])){
					if(is_array($v["params"])){
						$this->setParams($k,$v["params"]);
					}
				}
			}else{
				throw new \InvalidArgumentException(
					"Missing required index 'value' or value is not an array at offset $k"
				);
			}
		}
		$this->save();
	}

	/**
	 * @return bool True si les données ont été chargées depuis le fichier dbFile, false sinon
	 */
	protected final function loadedFromFile():bool{
		return $this->_loadedFromFile;
	}

	/**
	 * Enregistre les données du présent objet dans le fichier _dbFile
	 */
	public final function save():void{
		if($this->_modified){
			file_put_contents($this->_dbFile,$this->_serializer->serialize($this->_data),LOCK_EX);
			$this->_modified = false;
		}
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public final function current()
	{
		return $this->_data[$this->key()];
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public final function next()
	{
		$this->_cursor++;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public final function key()
	{
		return array_keys($this->_data)[$this->_cursor];
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public final function valid()
	{
		return count($this->_data) > $this->_cursor;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public final function rewind()
	{
		$this->_cursor = 0;
	}

	/**
	 * @param string $key Clé à tester
	 * @return bool True si la clé existe, false sinon
	 */
	public final function exists(string $key):bool{
		return isset($this->_data[$key]);
	}

	/**
	 * @param string $key Clé d'accès
	 * @return mixed Données
	 */
	public final function get(string $key)
	{
		return $this->_data[$key]["value"];
	}

	/**
	 * @param string $key Clé concernée
	 * @return array Paramètres de la clé
	 */
	public final function getParams(string $key): array
	{
		return $this->_data[$key]["params"];
	}

	/**
	 * Ajoute ou modifie une clé
	 *
	 * @param string $key  Clé d'accès
	 * @param mixed  $data Données
	 */
	public final function set(string $key, $data): void
	{
		if($this->exists($key)){
			$this->_data[$key]["value"] = $data;
		}else{
			$this->_data[$key] = ["value"=>$data,"params"=>[]];
		}
		$this->_modified = true;
	}

	/**
	 * @param string $key    Clé concernée
	 * @param array  $params Paramètres à appliquer
	 */
	public final function setParams(string $key, array $params): void
	{
		$atLeastOne = false;
		foreach($params as $k=>$v){
			$this->_data[$key]["params"][$k]=$v;
			$atLeastOne = true;
		}
		if($atLeastOne){
			$this->_modified = true;
		}
	}

	/**
	 * Supprime une clé.
	 *
	 * @param string $key Clé d'accès.
	 */
	public final function remove(string $key): void
	{
		unset($this->_data[$key]);
		$this->_modified = true;
	}

	/**
	 * Remet à 0 un tableau de paramètres pour une clé.
	 *
	 * @param string     $key    Clé concernée
	 * @param array|null $params (optionnel) Index des paramètres à supprimer
	 */
	public final function resetParams(string $key, ?array $params = null): void
	{
		$atLeastOne = false;
		if(is_null($params)){
			$this->_data = [];
			$atLeastOne = true;
		}else{
			foreach($params as $name){
				if(isset($this->_data[$key]['params'][$name])){
					unset($this->_data[$key]['params'][$name]);
					$atLeastOne = true;
				}
			}
		}
		if($atLeastOne){
			$this->_modified = true;
		}
	}

	/**
	 * @param $offset
	 * @return bool
	 */
	public final function offsetExists($offset)
	{
		return $this->exists($offset);
	}

	/**
	 * @param $offset
	 * @return mixed
	 */
	public final function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * @param $offset
	 * @param $value
	 */
	public final function offsetSet($offset,$value)
	{
		$this->set($offset,$value);
	}

	/**
	 * @param $offset
	 */
	public final function offsetUnset($offset) {
		$this->remove($offset);
	}
}