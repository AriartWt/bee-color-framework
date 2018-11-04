<?php
namespace wfw\engine\lib\PHP\objects;

use wfw\engine\lib\PHP\types\Type;

/**
 *  Permet des opérations sur les noms de classe
 */
class PHPClassName {
	/**
	 *  Namespace
	 * @var PHPNamespace $_namespace
	 */
	protected $_namespace;
	/**
	 *  Nom complet
	 * @var string $_fullName
	 */
	protected $_fullName;
	/**
	 *  Nom de la classe
	 * @var string $_name
	 */
	protected $_name;

	/**
	 *  Constructeur
	 *
	 * @param string $className Nom de la classe
	 */
	public function __construct(string $className)
	{
		$this->_fullName = $className;
		$tmp=explode("\\",$className);
		$this->_name = array_pop($tmp);

		if(count($tmp)>0){
			$this->_namespace = new PHPNamespace($tmp);
		}else{
			$this->_namespace = null;
		}
	}

	/**
	 *  Permet de savoir si la classe existe
	 * @return bool
	 */
	public function exists():bool{
		return class_exists($this->_fullName);
	}

	/**
	 *  Retourne le chemin d'accés à la classe
	 *
	 * @param bool $withoutExt
	 *
	 * @return string
	 */
	public function getFile(bool $withoutExt=false):string{
		return $this->getClassDirectory().DS.$this->_name.(($withoutExt)?"":".php");
	}

	/**
	 *  Obtient le chemin d'accés vers le dossier aprent d'un fichier de classe
	 * @return string
	 */
	public function getClassDirectory():string{
		return ROOT.DS.str_replace("\\",DS,$this->_namespace);
	}

	/**
	 *  Retourne le nom complet
	 * @return string
	 */
	public function getFullName():string{
		return $this->_fullName;
	}

	/**
	 *  Retourne le nom de la classe
	 * @return string
	 */
	public function getName():string{
		return $this->_name;
	}

	/**
	 *  Retourne le namespace s'il existe
	 * @return null|PHPNamespace
	 */
	public function getNamespace():?PHPNamespace{
		return $this->_namespace;
	}

	/**
	 *  Retourne le nom complet de la classe sous forme de tableau
	 * @return array
	 */
	public function toArray():array{
		$tmp = $this->getNamespace()->toArray();
		$tmp[]=$this->getName();
		return $tmp;
	}

	/**
	 *  to string
	 * @return string
	 */
	public function __toString()
	{
		return $this->_fullName;
	}

	/**
	 *  Permet de savoir si la classe courante étend la classe en paramètre
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public function extendsOrImplements(string $className){
		return (new Type($this->_fullName,true))->extendsOrImplements($className);
	}

	/**
	 *  Permet de savoir si le nom de classe complet courant est valide
	 * @return bool
	 */
	public function isValide():bool{
		return preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/",$this->_fullName);
	}
}