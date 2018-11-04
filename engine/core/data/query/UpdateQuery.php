<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête update
 */
class UpdateQuery extends SQLQuery implements IUpdateQuery {
	/** @var array $_set */
	protected $_set=[];
	/** @var string $_from */
	protected $_from;
	/** @var int $_limit */
	protected $_limit;

	/**
	 *  Transforme la requête courant en chaine de caractères
	 * @return string
	 */
	public function __toString(): string {
		$sep = " ";
		$res = "UPDATE ".$this->_from.$sep;
		if(count($this->_join)>0){
			foreach($this->_join as $type => $conds){
				$res.= "$type ".implode(" AND ",$conds).$sep;
			}
		}
		if(count($this->_set)>0){
			$res.= "SET ".implode(",",$this->_set).$sep;
		}
		if(count($this->_where)>0){
			$res.= "WHERE ".implode(" AND ",$this->_where).$sep;
		}
		if(!is_null($this->_limit)){
			$res.="LIMIT ".$this->_limit;
		}
		return $res.";";
	}

	/**
	 *  Ajoutes des valeures à insérer dans un INSERT ou un UPDATE
	 *
	 * @param string[] ...$asserts Valeures à insérer dans un INSERT ou un UPDATE
	 *
	 * @return IUpdateQuery
	 */
	public function set(string ...$asserts): IUpdateQuery {
		$this->_set = array_merge($this->_set,$asserts);
		return $this;
	}

	/**
	 *  Source des données
	 *
	 * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
	 *
	 * @return IUpdateQuery
	 */
	public function from($tableOrQuery): IUpdateQuery {
		$this->_from = $tableOrQuery;
		return $this;
	}

	/**
	 *  Permet de limiter le nombre d'update
	 *
	 * @param int $nb Nombre de résultat
	 *
	 * @return IUpdateQuery
	 */
	public function limit(int $nb): IUpdateQuery {
		$this->_limit = $nb;
		return $this;
	}

	/**
	 *  Where clause
	 *
	 * @param string[] ...$conditions Conditions
	 *
	 * @return IUpdateQuery
	 */
	public function where(string ...$conditions):IUpdateQuery {
		parent::where(...$conditions);
		return $this;
	}

	/**
	 * @param Valeur      $value
	 * @param null|string $key
	 *
	 * @return IUpdateQuery
	 */
	public function addParam($value, ?string $key = null):IUpdateQuery {
		parent::addParam($value, $key);
		return $this;
	}

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 *
	 * @param array $array Paramètres à ajouter
	 *
	 * @return IUpdateQuery
	 */
	public function addParams(array $array):IUpdateQuery {
		parent::addParams($array);
		return $this;
	}

	/**
	 * @param string   $type
	 * @param string[] ...$tablesAndCond
	 *
	 * @return IUpdateQuery
	 */
	public function join(string $type, string ...$tablesAndCond):IUpdateQuery {
		parent::join($type, ...$tablesAndCond);
		return $this;
	}
}