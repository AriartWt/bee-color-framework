<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/11/17
 * Time: 13:43
 */

namespace wfw\engine\core\data\query;

/**
 *  Requête select
 */
class SelectQuery extends SQLQuery implements ISelectQuery
{
    protected $_from;
    protected $_groupBy=[];
    protected $_orderBy=[];
    protected $_having=[];
    protected $_limit=[];
    protected $_clause;

    /**
     *     SelectQuery constructor.
     *
     * @param string[] $columns
     *
     * @internal param string $clause Clause de selection
     */
    public function __construct(string ...$columns)
    {
        $this->_clause = $columns;
    }

    /**
     *  Transforme la requête courant en chaine de caractères
     * @return string
     */
    public function __toString(): string
    {
        $sep = " ";
        $res = "SELECT ".implode(",",$this->_clause)."$sep FROM ".$this->_from.$sep;
        if(count($this->_join)>0){
            foreach($this->_join as $type=>$conds){
                $res.= "$type ".implode(" AND ",$conds).$sep;
            }
        }
        if(count($this->_where)>0){
            $res.= "WHERE ".implode(" AND ",$this->_where).$sep;
        }
        if(count($this->_having)>0){
            $res.="HAVING ".implode(" AND ",$this->_having).$sep;
        }
        if(count($this->_orderBy)>0){
            $res.="ORDER BY ".implode(",",$this->_orderBy).$sep;
        }
        if(count($this->_groupBy)>0){
            $res.="ORDER BY ".implode(",",$this->_orderBy).$sep;
        }
        if(count($this->_limit)>0){
            $res.="LIMIT ".$this->_limit[0]." OFFSET ".$this->_limit[1].$sep;
        }
        return $res;
    }

    /**
     *  Source des données
     *
     * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
     *
     * @return ISelectQuery
     */
    public function from($tableOrQuery): ISelectQuery
    {
        $this->_from = $tableOrQuery;
        return $this;
    }

    /**
     *  Ordonne les résultats
     *
     * @param string[] ...$columnAndCond nom de colonne + self::ASC ou self::DESC
     *
     * @return ISelectQuery
     */
    public function orderBy(string ...$columnAndCond): ISelectQuery
    {
        $this->_orderBy = array_merge($this->_orderBy,$columnAndCond);
        return $this;
    }

    /**
     *  Groupe des colonnes si leurs valeurs sont identiques
     *
     * @param string[] ...$columns Colonnes à grouper
     *
     * @return ISelectQuery
     */
    public function groupBy(string ...$columns): ISelectQuery
    {
        $this->_groupBy = array_merge($this->_groupBy,$columns);
        return $this;
    }

    /**
     *  IDEM que WHERE mais permet de filtrer sur des fonctions
     *
     * @param string[] ...$havingClauses exemple : "SUM(colName) > 4"
     *
     * @return ISelectQuery
     */
    public function having(string ...$havingClauses): ISelectQuery
    {
        $this->_having = array_merge($this->_having,$havingClauses);
        return $this;
    }

    /**
     *  Permet de limiter le nombre d'affichages
     *
     * @param int $nb     Nombre de résultat
     * @param int $offset Offset ( range = $offset / $nb + $offset )
     *
     * @return ISelectQuery
     */
    public function limit(int $nb, int $offset = 0): ISelectQuery
    {
        $this->_limit = [$nb,$offset];
        return $this;
    }

    /**
     *  Where clause
     * @param string[] ...$conditions Conditions
     *
     * @return ISelectQuery
     */
    public function where(string ...$conditions):ISelectQuery
    {
        parent::where(...$conditions);
        return $this;
    }

    /**
     * @param mixed       $value
     * @param null|string $key
     *
     * @return ISelectQuery
     */
    public function addParam($value, ?string $key = null):ISelectQuery
    {
        parent::addParam($value, $key);
        return $this;
    }

    /**
     *  Permet d'ajouter des paramètres sous forme de tableau
     *
     * @param array $array Paramètres à ajouter
     *
     * @return ISelectQuery
     */
    public function addParams(array $array):ISelectQuery
    {
        parent::addParams($array);
        return $this;
    }

    /**
     * @param string   $type
     * @param string[] ...$tablesAndCond
     *
     * @return ISelectQuery
     */
    public function join(string $type, string ...$tablesAndCond):ISelectQuery
    {
        parent::join($type, ...$tablesAndCond);
        return $this;
    }
}