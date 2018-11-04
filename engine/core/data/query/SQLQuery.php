<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête SQL de base
 */
abstract class SQLQuery implements ISQLQuery {
	/** @var array $_params */
	protected $_params=[];
	/** @var array $_where */
	protected $_where=[];
	/** @var array $_join */
	protected $_join=[];

	/**
	 *  Retourne les paramètres de la requêtes (paramètres bindés) sour forme de tableau "clé","valeur"
	 * @return array
	 */
	public function getParams(): array {
		return $this->_params;
	}

	/**
	 *  Compile la requête courante
	 * @return CompiledQuery
	 */
	public function compile(): CompiledQuery {
		return new CompiledQuery($this,$this->_params);
	}

	/**
	 *  Permet d'ajouter des paramètres à binder à la requête
	 *
	 * @param mixed       $value Valeur à binder
	 * @param null|string $key   (optionnel) Si non précisé, l'ordre est pris en compte
	 */
	public function addParam($value, ?string $key = null) {
		if(!is_null($key)){
			$this->_params[$key]=$value;
		}else{
			$this->_params[]=$value;
		}
	}

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 * @param array $array Paramètres à ajouter
	 */
	public function addParams(array $array){
		$this->_params = array_merge($this->_params,$array);
	}

	/**
	 *  Ajoute des clauses where
	 *
	 * @param string[] ...$conditions Conditions (séparée par des AND, pour des 0R grouper les conditions
	 */
	public function where(string ...$conditions) {
		$this->_where = array_merge($this->_where,$conditions);
	}

	/**
	 *  Jointure sur plusieurs tableas
	 *
	 * @param string   $type             Type de jointure
	 * @param string[] ...$tablesAndCond Tables et conditions de jointure
	 */
	public function join(string $type, string ...$tablesAndCond) {
		if(!isset($this->_join[$type])){
			$this->_join[$type]=$tablesAndCond;
		}else{
			$this->_join[$type] = array_merge($this->_join[$type],$tablesAndCond);
		}
	}
}