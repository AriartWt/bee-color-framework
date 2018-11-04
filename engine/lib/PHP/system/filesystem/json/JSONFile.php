<?php
namespace wfw\engine\lib\PHP\system\filesystem\json;

use Exception;

/**
 *  Manipule un fichier JSON
 */
final class JSONFile {
	/**
	 *  Chemin d'accés au fichier json
	 * @var string $_path
	 */
	private $_path;
	/**
	 *  Option d'écriture des données JSON dans le fichier
	 * @var int $_printOpt
	 */
	private $_printOpt;

	/**
	 *  JsonFile constructor.
	 *
	 * @param string $path     Chemin d'accés au fichier
	 * @param int    $printOpt Format d'écriture du JSON (par défaut : JSON_PRETTY_PRINT)
	 */
	public function __construct(string $path,int $printOpt = JSON_PRETTY_PRINT) {
		$this->_path = $path;
		$this->_printOpt = $printOpt;
	}

	/**
	 *  Ecrit les données dans le fichier JSON
	 * @param mixed $data Données
	 */
	public function write($data){
		file_put_contents($this->_path,json_encode($data,$this->_printOpt));
	}

	/**
	 *  Renvoie les données lues dans le fichier courant
	 *
	 * @param bool $assoc (optionnel défaut : false) Format des données renvoyées. Si true : tableau associatif, sinon stdClass
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function read(bool $assoc = false){
		if(file_exists($this->_path)){
			$res = json_decode(file_get_contents($this->_path),$assoc);
			if(json_last_error() !== JSON_ERROR_NONE)
				throw new Exception("json_decode() : ".json_last_error_msg()." (code ".json_last_error().")");
			return $res;
		}else{
			throw new Exception("Unable to read $this->_path : file does'nt exists");
		}
	}

	/**
	 *  banalise les quote et les backslash pour l'affichage des chaines JSON
	 *
	 * @param  string $str chaine à banaliser
	 *
	 * @return string      chaine banalisée
	 */
	public function backslash(string $str):string{
		$str=str_replace('\\','\\\\',$str);
		$str=str_replace("'","\\'",$str);
		return $str;
	}
}