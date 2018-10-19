<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/11/17
 * Time: 09:01
 */

namespace wfw\engine\core\data\query;

use wfw\engine\core\data\query\errors\SQLQueryFailure;

/**
 *  Requête SQL brute. Immutable.
 */
final class RawQuery extends SQLQuery
{
    private $_query;

    /**
     *  RawQuery constructor.
     *
     * @param string $query Requête
     */
    public function __construct(string $query)
    {
        $this->_query = $query;
    }

    /**
     *  Transforme la requête courant en chaine de caractères
     * @return string
     */
    public function __toString(): string
    {
        return $this->_query;
    }

    /**
     *  Ajoute un paramètre à la requête
     * @param mixed       $value Valeur
     * @param null|string $key   Clé
     *
     * @return RawQuery
     */
    public function addParam($value, ?string $key = null):RawQuery
    {
        parent::addParam($value, $key);
        return $this;
    }

    /**
     *  Ajouteune liste de paramètres
     * @param array $array Paramètres
     *
     * @return RawQuery
     */
    public function addParams(array $array):RawQuery
    {
        parent::addParams($array);
        return $this;
    }

    /**
     *  Retourne une exception dans tous les cas. Une clause where est inutile pour une requête brute.
     *
     * @param string[] ...$conditions
     *
     * @throws SQLQueryFailure
     */
    public function where(string ...$conditions)
    {
        throw new SQLQueryFailure("RAW SQL query does'nt support where statement !");
    }

    /**
     *  Retourne une exception dans tous les cas. Une clause join est inutile pour une requête brute.
     *
     * @param string   $type
     * @param string[] ...$tablesAndCond
     *
     * @throws SQLQueryFailure
     */
    public function join(string $type, string ...$tablesAndCond)
    {
        throw new SQLQueryFailure("RAW SQL query does'nt support join statement !");
    }
}