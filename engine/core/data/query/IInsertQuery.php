<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/11/17
 * Time: 08:31
 */

namespace wfw\engine\core\data\query;

/**
 *  Requête d'insertion
 */
interface IInsertQuery extends ISQLQuery
{
    /**
     *  Destination de l'insertion
     *
     * @param string $tableName Nom de la table concernée par l'insertion
     *
     * @return IInsertQuery
     */
    public function into(string $tableName):IInsertQuery;

    /**
     *  Précise les colonnes
     *
     * @param string[] ...$columns Noms de colonnes
     *
     * @return IInsertQuery
     */
    public function columns(string ...$columns):IInsertQuery;

    /**
     *  Permet d'ajouter une ligne de valeurs (doit correspondre au nombre de colonnes si définies) Chaque appel est une ligne supplémentaire.
     *
     * @param string[] ...$values Ligne de valeurs
     *
     * @return IInsertQuery
     */
    public function values(string ...$values):IInsertQuery;

    /**
     *  Where clause
     *
     * @param string[] ...$conditions Conditions
     *
     * @return IInsertQuery
     * @throws SQLQueryException
     */
    public function where(string ...$conditions):IInsertQuery;

    /**
     * @param Valeur      $value
     * @param null|string $key
     *
     * @return IInsertQuery
     */
    public function addParam($value, ?string $key = null):IInsertQuery;

    /**
     *  Permet d'ajouter des paramètres sous forme de tableau
     *
     * @param array $array Paramètres à ajouter
     *
     * @return IInsertQuery
     */
    public function addParams(array $array):IInsertQuery;

    /**
     * @param string   $type
     * @param string[] ...$tablesAndCond
     *
     * @return IInsertQuery
     * @throws SQLQueryException
     */
    public function join(string $type, string ...$tablesAndCond):IInsertQuery;
}