<?php
namespace wfw\engine\lib\PHP\types;

/**
 *  Simple enumerateur
 */
abstract class PHPEnum {
	/**
	 * a construction d'un enum n'a aucun sens.
	 */
	private function __construct() {}

	/**
	 * @param string $mode
	 *
	 * @return mixed
	 */
	public static function get(string $mode){
		$reflection = new \ReflectionClass(static::class);
		$res = $reflection->getConstants();
		if(!isset($res[$mode])){
			throw new \InvalidArgumentException("Constant $mode not found in ".static::class);
		}else{
			return $res[$mode];
		}
	}

	/**
	 * @return array Liste de toutes les constantes de l'Enum
	 */
	public static function getAll():array{
		return (new \ReflectionClass(static::class))->getConstants();
	}

	/**
	 *  Retourne le nom d'une constante grâce à sa valeur
	 *
	 * @param mixed $value Valeur à tester
	 *
	 * @return string
	 */
	public static function getName($value):string{
		$reflection = new \ReflectionClass(static::class);
		$res = $reflection->getConstants();
		foreach($res as $constName=>$v){
			if($value === $v){
				return $constName;
			}
		}
		throw new \InvalidArgumentException("No class constant defined for this value !");
	}

	/**
	 *  Teste l'existence d'une clé
	 *
	 * @param string $key Nom de la clé à tester
	 *
	 * @return bool
	 */
	public static function exists(string $key):bool{
		$reflection = new \ReflectionClass(static::class);
		$res = $reflection->getConstants();
		return isset($res[$key]);
	}

	/**
	 *  Teste l'existence d'une valeur
	 * @param mixed $value Valeur à tester
	 *
	 * @return bool
	 */
	public static function existsValue($value):bool{
		$reflection = new \ReflectionClass(static::class);
		$res = $reflection->getConstants();
		foreach($res as $constName=>$v){
			if($value === $v){
				return true;
			}
		}
		return false;
	}
}