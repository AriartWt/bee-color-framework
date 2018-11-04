<?php 
namespace wfw\engine\lib\PHP\objects;

use Exception;
use stdClass;
use Traversable;

/**
 *  Permet des recherches par clé dans des objets (recursif)
 */
final class StdClassOperator implements \IteratorAggregate {
	/** @var stdClass $_obj */
	private $_obj;

	/**
	 * StdClassOperator constructor.
	 *
	 * @param stdClass $obj Objet à gérer.
	 */
	public function __construct(\stdClass $obj){
		$this->_obj = $obj;
	}

	/**
	 * Trouve la valeur associée à un chemin à travers l'objet.
	 *
	 * stdClass{prop1->stdClass{subProp2->stdClass{subProp3 = 389}}};
	 * find("prop1/subProp2/subProp3") -> 389
	 *
	 * @param string $pathInObject Chemin à travers l'objet
	 * @return mixed Valeur de la propriété
	 * @throws Exception si $path ne correspond pas à un chemin à travers les propriétés de l'objet.
	 */
	public function find(string $pathInObject){
		$path=explode('/',$pathInObject);
		$res=$this->_obj;
		for($i=0;$i<count($path);$i++){
			if(method_exists($res,$path[$i])){
				$res=$res->$path[$i]();
			}else if(isset($res->{$path[$i]})){
				$res=$res->{$path[$i]};
			}else{
				throw new Exception("Cannot find $pathInObject");
			}
		}
		return $res;
	}

	/**
	 * @return array
	 */
	public function toArray():array{
		return self::stdClassToArray($this->_obj);
	}

	/**
	 * @param stdClass $obj
	 * @return array
	 */
	private static function stdClassToArray(stdClass $obj):array{
		$res = get_object_vars($obj);
		foreach($res as $k=>$v){
			if($v instanceof stdClass){
				$res[$k]=self::stdClassToArray($v);
			}else if(is_array($v)){
				self::checkForStdClass($res[$k]);
			}
		}
		return $res;
	}

	/**
	 * @param array $array
	 */
	private static function checkForStdClass(array &$array){
		foreach($array as $k=>$v){
			if($v instanceof stdClass){
				$array[$k] = self::stdClassToArray($v);
			}else if(is_array($v)){
				self::checkForStdClass($v);
			}
		}
	}

	/**
	 * @param stdClass $obj Objet à fusionner à l'objet courant.
	 */
	public function mergeStdClass(\stdClass $obj){
		self::deepMerge($this->_obj,$obj);
	}

	/**
	 * @return stdClass Copie de l'objet courant
	 */
	public function getStdClassCopy():stdClass{
		$res = new stdClass();
		self::deepMerge($res,$this->_obj);
		return $res;
	}

	/**
	 * @return stdClass Objet courant
	 */
	public function getStdClass():stdClass{
		return $this->_obj;
	}

	/**
	 *  Fusionne de manière recursive deux objets
	 *
	 * @param stdClass $dest Destination de la copie
	 * @param stdClass $obj  Objet à copier
	 */
	private static function deepMerge(stdClass $dest, stdClass $obj):void{
		foreach($obj as $k=>$v){
			if(!isset($dest->$k)){
				if(is_object($v)){
					$dest->$k = new stdClass();
					self::deepMerge($dest->$k, $v);
				}else{
					$dest->$k=$v;
				}
			}else{
				if(is_object($dest->$k) && is_object($v)){
					self::deepMerge($dest->$k, $v);
				}else{
					$dest->$k=$v;
				}
			}
		}
	}

	/**
	 * Converti recursivement tous les tableaux contenus dans $arr en stdClass si le tableau contient
	 * au moins une clé nom numérique
	 * @param array $arr Tableau à convertir
	 * @return stdClass|array
	 */
	public static function arrayToStdClass(array $arr){
		$stringkey = false;
		foreach($arr as $k=>$v){
			if(!is_integer($k)) $stringkey = true;
			$arr[$k] = $v;
			if($v instanceof stdClass){
				$arr[$k] = new stdClass();
				self::deepMerge($arr[$k],$v);
				foreach($v as $i=>$w){
					if(is_array($w)) $v->$i=self::arrayToStdClass($w);
				}
			}else if(is_array($v)) $arr[$k] = self::arrayToStdClass($v);
		}
		if($stringkey){
			$res = new stdClass();
			foreach($arr as $k=>$v){
				$res->$k = $v;
			}
			return $res;
		}else return $arr;
	}

	/**
	 * @param string $name Nom de la propriété
	 * @return mixed
	 */
	public function __get($name){
		return $this->_obj->$name ?? null;
	}

	/**
	 * @param string $name Nom de la propriété
	 * @param mixed $value Valeur
	 */
	public function __set($name, $value){
		$this->_obj->$name = $value;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator(){ return new \ArrayObject($this->_obj); }
}

 