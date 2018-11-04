<?php
namespace wfw\engine\core\data\query;

use wfw\engine\core\data\query\errors\SQLQueryFailure;

/**
 *  Requête d'insertion
 */
class InsertQuery extends SQLQuery implements IInsertQuery {
	/** @var string $_into */
	protected $_into;
	/** @var array $_values */
	protected $_values=[];
	/** @var array $_columns */
	protected $_columns=[];

	/**
	 *  Destination de l'insertion
	 *
	 * @param string $tableName Nom de la table concernée par l'insertion
	 *
	 * @return IInsertQuery
	 */
	public function into(string $tableName): IInsertQuery {
		$this->_into = $tableName;
		return $this;
	}

	/**
	 *  Précise les colonnes
	 *
	 * @param string[] ...$columns Noms de colonnes
	 *
	 * @return IInsertQuery
	 */
	public function columns(string ...$columns): IInsertQuery {
		$this->_columns = array_merge($this->_columns,$columns);
		return $this;
	}

	/**
	 *  Permet d'ajouter une ligne de valeurs (doit correspondre au nombre de colonnes si définies) Chaque appel est une ligne supplémentaire.
	 *
	 * @param string[] ...$values Ligne de valeurs
	 *
	 * @return IInsertQuery
	 */
	public function values(string ...$values): IInsertQuery {
		$this->_values[] = $values;
		return $this;
	}

	/**
	 *  Transforme la requête courant en chaine de caractères
	 * @return string
	 */
	public function __toString(): string {
		$sep = " ";
		$res = "INSERT INTO $this->_into".$sep;
		if(count($this->_columns) > 0){
			$res.="(`".implode("`,`",$this->_columns)."`)".$sep;
		}
		$res.="VALUES ";
		$last = count($this->_values)-1;
		foreach($this->_values as $k=>$v){
			$res.="(".implode(',',$v).")".(($last !== $k)?",":"").$sep;
		}
		return $res.";";
	}

	/**
	 *  Where clause
	 *
	 * @param string[] ...$conditions Conditions
	 *
	 * @return IInsertQuery
	 * @throws SQLQueryFailure
	 */
	public function where(string ...$conditions):IInsertQuery {
		throw new SQLQueryFailure("WHERE clause is not allowed for INSERT statement");
	}

	/**
	 * @param Valeur      $value
	 * @param null|string $key
	 *
	 * @return IInsertQuery
	 */
	public function addParam($value, ?string $key = null):IInsertQuery {
		parent::addParam($value, $key);
		return $this;
	}

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 *
	 * @param array $array Paramètres à ajouter
	 *
	 * @return IInsertQuery
	 */
	public function addParams(array $array):IInsertQuery {
		parent::addParams($array);
		return $this;
	}

	/**
	 * @param string   $type
	 * @param string[] ...$tablesAndCond
	 *
	 * @return IInsertQuery
	 * @throws SQLQueryFailure
	 */
	public function join(string $type, string ...$tablesAndCond):IInsertQuery {
		throw new SQLQueryFailure("Join statement is not allowed for INSERT statement");
	}
}