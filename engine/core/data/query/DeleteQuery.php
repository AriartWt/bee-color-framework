<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/11/17
 * Time: 13:57
 */

namespace wfw\engine\core\data\query;

/**
 *  Requête Delete
 */
class DeleteQuery extends SQLQuery implements IDeleteQuery
{
    protected $_from;
    protected $_limit;

    /**
     *  Source des données
     *
     * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
     *
     * @return IDeleteQuery
     */
    public function from($tableOrQuery): IDeleteQuery
    {
        $this->_from = $tableOrQuery;
        return $this;
    }

    /**
     *  Permet de limiter le nombre d'update
     *
     * @param int $nb Nombre de résultat
     *
     * @return IDeleteQuery
     */
    public function limit(int $nb): IDeleteQuery
    {
        $this->_limit = $nb;
        return $this;
    }

    /**
     *  Transforme la requête courant en chaine de caractères
     * @return string
     */
    public function __toString(): string
    {
        $sep = " ";
        $res = "DELETE FROM ".$this->_from.$sep;
        if(count($this->_join)>0){
            foreach($this->_join as $type=>$conds){
                $res.= "$type ".implode(" AND ",$conds).$sep;
            }
        }
        if(count($this->_where)>0){
            $res.= "WHERE ".implode(",",$this->_where).$sep;
        }
        if(!is_null($this->_limit)){
            $res.= "LIMIT ".$this->_limit;
        }
        return $res.";";
    }

    /**
     *  Where clause
     *
     * @param string[] ...$conditions Conditions
     *
     * @return IDeleteQuery
     */
    public function where(string ...$conditions):IDeleteQuery
    {
        parent::where(...$conditions);
        return $this;
    }

    /**
     * @param mixed       $value
     * @param null|string $key
     *
     * @return IDeleteQuery
     */
    public function addParam($value, ?string $key = null):IDeleteQuery
    {
        parent::addParam($value, $key);
        return $this;
    }

    /**
     *  Permet d'ajouter des paramètres sous forme de tableau
     *
     * @param array $array Paramètres à ajouter
     *
     * @return IDeleteQuery
     */
    public function addParams(array $array) : IDeleteQuery
    {
        parent::addParams($array);
        return $this;
    }

    /**
     * @param string   $type
     * @param string[] ...$tablesAndCond
     *
     * @return IDeleteQuery
     */
    public function join(string $type, string ...$tablesAndCond):IDeleteQuery
    {
        parent::join($type, ...$tablesAndCond);
        return $this;
    }
}