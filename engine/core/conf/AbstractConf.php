<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/01/18
 * Time: 07:07
 */

namespace wfw\engine\core\conf;

use stdClass;
use wfw\engine\lib\errors\InvalidTypeSupplied;
use wfw\engine\lib\PHP\objects\StdClassOperator;

/**
 *  Configuration de base
 */
abstract class AbstractConf implements IConf
{
	/**
	 *  Permet de sauvegarder automatiquement les configurations lors d'une modification
	 * @var bool $_autoSave
	 */
	protected $_autoSave=false;

	/**
	 *  Liste des configurations ajoutées à la configuration courante
	 * @var IConf[] $_confMerges
	 */
	protected $_confMerges;

	/**
	 *  Objet de configuration parsé
	 * @var stdClass $_conf
	 */
	protected $_conf;

	/**
	 * @var StdClassOperator $_stdOperator
	 */
	private $_stdOperator;

	/**
	 * AbstractConf constructor.
	 *
	 * @param stdClass $rawConf
	 */
	public function __construct(stdClass $rawConf)
	{
		$this->_confMerges = [];
		$this->_conf = $rawConf;
		$this->_stdOperator = new StdClassOperator($rawConf);
	}

	/**
	 *  Retourne l'objet stdClass contenant les configurations
	 * @return stdClass
	 */
	public function getRawConf():stdClass{
		return $this->_conf;
	}

	/**
	 *  Permet de savoir si l'instance courante est en mode sauvegarde automatique
	 * @return bool
	 */
	public function autoSaveModeEnabled(): bool
	{
		return $this->_autoSave;
	}

	/**
	 *  Permet de changer l'état de la sauvegarde automatique
	 * @param bool $auto Nouveau mode de sauvegarde
	 */
	public function setAutoSaveMode(bool $auto):void{
		$this->_autoSave=$auto;
	}

	/**
	 *  Intégre (fusionne) la configuration passée en paramètre avec la configuration courante
	 *
	 * @param IConf $conf Configuration à intégrer
	 */
	public function merge(IConf $conf):void{
		if(!in_array($conf,$this->_confMerges)){
			$this->_confMerges[] = $conf;
		}
		$this->_stdOperator->mergeStdClass($conf->getRawConf());
	}

	/**
	 *  Reconstruit une configuration en réappliquant les données de chaque configurations dans la liste _confMerges
	 */
	public function rebuild():void{
		foreach($this->_confMerges as $conf){
			$this->merge($conf);
		}
	}
	/**
	 *  Retourne une configuration
	 *
	 * @param string $path Clé d'accés à la valeur de la configuration
	 *
	 * @return mixed
	 */
	public function get(string $path){
		if(strlen($path) === 0) return $this->getRawConf();
		$path=explode("/",$path);
		$current = $this->getRawConf();
		foreach($path as $v){
			if(isset($current->$v)){
				$current=$current->$v;
			}else{
				$current=null;
			}
		}
		return $current;
	}

	/**
	 *  Renvoie true si la clé de configuratione existe, false sinon
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 */
	public function existsKey(string $key):bool{
		$path=explode("/",$key);
		$current = $this->getRawConf();

		foreach($path as $k=>$v){
			if(isset($current->$v)){
				$current=$current->$v;
			}else{
				return false;
			}
		}
		return true;
	}

	/**
	 *  Supprime une clé de configuration
	 *
	 * @param string $key Clé à supprimer
	 */
	public function removeKey(string $key){
		$path=explode("/",$key);
		$current = $this->getRawConf();
		$last = count($path)-1;
		foreach($path as $k=>$v){
			if(isset($current->$v)){
				if($last !== $k){
					$current=$current->$v;
				}else{
					unset($current->$v);
				}
			}else{
				throw new \InvalidArgumentException("$key doesn't exists and can't be removed !");
			}
		}
	}

	/**
	 *  Modifie une clé de configuration et l'enregistre
	 * @param string $path  Clé de configuration à modifier
	 * @param mixed  $value Nouvelle valeur de la clé de configuration
	 */
	public function set(string $path, $value):void{
		$path=explode("/",$path);
		$current=$this->getRawConf();

		$parent=new stdClass();
		foreach($path as $v){
			$parent->obj=$current;
			$parent->key=$v;
			if(isset($current->$v)){
				$current=$current->$v;
			}else{
				$current->$v=new stdClass();
				$current=$current->$v;
			}
		}
		if($path[count($path)-1]==$parent->key){
			if(!$parent->obj){
				$parent->obj=new stdClass();
			}
			$parent->obj->{$parent->key}=$value;
		}

		if($this->autoSaveModeEnabled()){
			$this->save();
		}
	}

	/**
	 *  Retourne une clé de configuration booléenne
	 *
	 * @param string $key Clé
	 *
	 * @return bool|null
	 */
	public function getBoolean(string $key):?bool{
		$res=$this->get($key);
		if(is_null($res)){
			return null;
		}else{
			return filter_var($res,FILTER_VALIDATE_BOOLEAN);
		}
	}

	/**
	 *  Retourne une clé de configuration entière
	 *
	 * @param string $key Clé
	 *
	 * @return int|null
	 */
	public function getInt(string $key):?int{
		$res=$this->get($key);
		if(is_null($res)){
			return null;
		}else{
			return filter_var($res,FILTER_VALIDATE_INT);
		}
	}

	/**
	 *  Retourne une clé de configuration chaine de cractère
	 * @param string $key Clé
	 *
	 * @return null|string
	 */
	public function getString(string $key):?string{
		$res=$this->get($key);
		if(is_null($res)){
			return null;
		}else{
			return $res;
		}
	}

	/**
	 *  Retourne la valeur d'un clé de configuration float
	 * @param string $key Clé
	 *
	 * @return float|null
	 */
	public function getFloat(string $key):?float{
		$res=$this->get($key);
		if(is_null($res)){
			return null;
		}else{
			return filter_var($res,FILTER_VALIDATE_FLOAT);
		}
	}

	/**
	 *  Retourne la valeur d'une clé de configuration stdClass
	 *
	 * @param string $key Clé
	 *
	 * @return null|stdClass
	 * @throws InvalidTypeSupplied
	 */
	public function getObject(string $key):?stdClass{
		$conf = $this->get($key);
		if($conf instanceof stdClass || is_null($conf)){
			return $conf;
		}else{
			throw new InvalidTypeSupplied("$key is not an object");
		}
	}

	/**
	 *  Retourne un tableau
	 *
	 * @param string $key
	 *
	 * @return array|null
	 */
	public function getArray(string $key):?array
	{
		$res = $this->get($key);
		if(is_null($res)){
			return null;
		}else{
			if(is_object($res)){
				return (array) $res;
			}else{
				return $res;
			}
		}
	}
}